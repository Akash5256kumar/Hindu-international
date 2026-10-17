/* =====================================================================
   ADMIN PANEL — content editor
   Builds the editing form from the schema returned by api.php, keeps a
   working copy of the content, and saves it back (server validates).
   No HTML from content is ever inserted with innerHTML.
   ===================================================================== */

(function () {
  'use strict';

  var CSRF = document.querySelector('meta[name="csrf-token"]').content;
  var sidebar = document.getElementById('sidebar');
  var editor = document.getElementById('editor');
  var saveBtn = document.getElementById('save-btn');
  var saveStatus = document.getElementById('save-status');
  var toastEl = document.getElementById('toast');

  var state = {
    schema: [],
    content: {},
    savedSnapshot: '',
    section: null,
    errors: {},       // path -> message (from the last save attempt)
    uploading: 0
  };

  /* =================================================================
     Helpers
     ================================================================= */

  /** Tiny DOM builder: h('div', {class: 'x', onclick: fn}, [children]) */
  function h(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (key) {
      var val = attrs[key];
      if (val == null || val === false) return;
      if (key.slice(0, 2) === 'on') node.addEventListener(key.slice(2), val);
      else if (key === 'text') node.textContent = val;
      else if (key in node && key !== 'list' && key !== 'form') node[key] = val;
      else node.setAttribute(key, val === true ? '' : val);
    });
    [].concat(children || []).forEach(function (child) {
      if (child == null || child === false) return;
      node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
    });
    return node;
  }

  function joinPath(base, key) { return base ? base + '.' + key : String(key); }

  function getPath(obj, path) {
    if (!path) return obj;
    return path.split('.').reduce(function (o, k) { return o == null ? undefined : o[k]; }, obj);
  }

  function setPath(obj, path, value) {
    var keys = path.split('.');
    var last = keys.pop();
    var target = keys.reduce(function (o, k, i) {
      if (o[k] == null || typeof o[k] !== 'object') o[k] = /^\d+$/.test(keys[i + 1] || last) ? [] : {};
      return o[k];
    }, obj);
    target[last] = value;
  }

  function clone(v) { return JSON.parse(JSON.stringify(v)); }

  function toast(message, type) {
    toastEl.textContent = message;
    toastEl.className = 'toast toast--' + (type || 'info');
    toastEl.hidden = false;
    clearTimeout(toast.timer);
    toast.timer = setTimeout(function () { toastEl.hidden = true; }, type === 'error' ? 7000 : 3500);
  }

  function mediaUrl(path) { return '../' + path; }

  /* =================================================================
     API
     ================================================================= */

  function request(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = Object.assign({ 'X-CSRF-Token': CSRF }, options.headers || {});
    return fetch(url, options).then(function (res) {
      return res.json().catch(function () { return { ok: false, message: 'Unexpected server response (' + res.status + ').' }; })
        .then(function (data) {
          if (res.status === 401) sessionExpired(data.message);
          data.status = res.status;
          return data;
        });
    }, function () {
      return { ok: false, message: 'Could not reach the server. Check your internet connection.' };
    });
  }

  function postJson(action, body) {
    return request('api.php?action=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
  }

  /** Upload with progress (XHR, because fetch has no upload progress) */
  function upload(file, kind, onProgress) {
    return new Promise(function (resolve) {
      var form = new FormData();
      form.append('file', file);
      form.append('kind', kind);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'api.php?action=upload');
      xhr.setRequestHeader('X-CSRF-Token', CSRF);
      xhr.upload.onprogress = function (e) { if (e.lengthComputable) onProgress(e.loaded / e.total); };
      xhr.onload = function () {
        var data;
        try { data = JSON.parse(xhr.responseText); } catch (e) { data = { ok: false, message: 'Upload failed (' + xhr.status + ').' }; }
        if (xhr.status === 401) sessionExpired(data.message);
        resolve(data);
      };
      xhr.onerror = function () { resolve({ ok: false, message: 'Upload failed. Check your internet connection.' }); };
      xhr.send(form);
    });
  }

  function sessionExpired(message) {
    toast((message || 'Your session has expired.') + ' Redirecting to login…', 'error');
    window.removeEventListener('beforeunload', warnUnsaved);
    setTimeout(function () { location.href = 'login.php'; }, 2500);
  }

  /* =================================================================
     Dirty state + saving
     ================================================================= */

  function isDirty() { return JSON.stringify(state.content) !== state.savedSnapshot; }

  function refreshSaveState() {
    var dirty = isDirty();
    saveBtn.disabled = !dirty || state.uploading > 0;
    saveStatus.textContent = state.uploading ? 'Uploading…' : (dirty ? 'Unsaved changes' : 'All changes saved');
    saveStatus.className = 'save-status' + (dirty ? ' is-dirty' : '');
  }

  function changed() { refreshSaveState(); }

  function warnUnsaved(e) {
    if (!isDirty()) return;
    e.preventDefault();
    e.returnValue = '';
  }

  function save() {
    if (saveBtn.disabled) return;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
    postJson('save', { content: state.content }).then(function (res) {
      saveBtn.textContent = 'Save changes';
      if (res.ok) {
        state.content = res.content;
        state.savedSnapshot = JSON.stringify(res.content);
        state.errors = {};
        renderSidebar();
        renderSection(state.section);
        toast('Saved. Your changes are now live on the website.', 'success');
      } else {
        state.errors = res.errors || {};
        var first = Object.keys(state.errors)[0];
        renderSidebar();
        if (first) {
          renderSection(sectionForPath(first) || state.section);
          focusField(first);
        }
        toast(res.message || 'Could not save.', 'error');
      }
      refreshSaveState();
    });
  }

  /* =================================================================
     Navigation
     ================================================================= */

  /** Which sidebar section does a content path belong to? */
  function sectionForPath(path) {
    var found = null;
    state.schema.forEach(function (section) {
      section.groups.forEach(function (group) {
        group.fields.forEach(function (field) {
          var prefix = joinPath(group.path, field.key);
          if (path === prefix || path.indexOf(prefix + '.') === 0) found = found || section.id;
        });
      });
    });
    return found;
  }

  function errorCount(sectionId) {
    return Object.keys(state.errors).filter(function (p) { return sectionForPath(p) === sectionId; }).length;
  }

  function renderSidebar() {
    sidebar.textContent = '';
    var list = h('ul', { class: 'sidebar__list' });
    state.schema.forEach(function (section) {
      var count = errorCount(section.id);
      list.appendChild(h('li', {}, [
        h('a', {
          class: 'sidebar__link' + (section.id === state.section ? ' is-active' : ''),
          href: '#' + section.id,
          'aria-current': section.id === state.section ? 'page' : null
        }, [section.title, count ? h('span', { class: 'sidebar__badge', text: String(count), title: count + ' to fix' }) : null])
      ]));
    });
    sidebar.appendChild(list);
  }

  function focusField(path) {
    var node = editor.querySelector('[data-path="' + CSS.escape(path) + '"]');
    if (!node) return;
    var details = node.closest('details');
    if (details) details.open = true;
    node.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var input = node.querySelector('input, textarea, button');
    if (input) setTimeout(function () { input.focus({ preventScroll: true }); }, 300);
  }

  /* =================================================================
     Rendering the form
     ================================================================= */

  function renderSection(id) {
    var section = state.schema.filter(function (s) { return s.id === id; })[0] || state.schema[0];
    state.section = section.id;
    renderSidebar();

    editor.textContent = '';
    editor.appendChild(h('h1', { class: 'editor__title', text: section.title }));
    section.groups.forEach(function (group) {
      var body = h('div', { class: 'card__body' });
      group.fields.forEach(function (field) { body.appendChild(renderField(field, joinPath(group.path, field.key))); });
      editor.appendChild(h('section', { class: 'card' }, [
        h('header', { class: 'card__head' }, [
          h('h2', { class: 'card__title', text: group.title }),
          group.help ? h('p', { class: 'card__help', text: group.help }) : null
        ]),
        body
      ]));
    });
    editor.focus({ preventScroll: true });
    window.scrollTo(0, 0);
  }

  function renderField(field, path) {
    if (field.type === 'list') return renderList(field, path);

    var id = 'f-' + path.replace(/\./g, '-');
    var error = state.errors[path];
    var wrap = h('div', { class: 'field' + (error ? ' has-error' : ''), 'data-path': path });
    var label = h('label', { class: 'field__label', for: id }, [field.label, field.required ? h('span', { class: 'req', text: ' *' }) : null]);

    var control;
    switch (field.type) {
      case 'image':
      case 'video':
        control = renderMedia(field, path, id);
        label = h('span', { class: 'field__label', id: id + '-label' }, [field.label, field.required ? h('span', { class: 'req', text: ' *' }) : null]);
        break;
      case 'toggle':
        control = h('label', { class: 'switch' }, [
          h('input', {
            type: 'checkbox', id: id,
            checked: getPath(state.content, path) === undefined ? !!field.default : !!getPath(state.content, path),
            onchange: function (e) { setPath(state.content, path, e.target.checked); changed(); }
          }),
          h('span', { class: 'switch__track', 'aria-hidden': 'true' }),
          h('span', { class: 'switch__text', text: field.label })
        ]);
        label = null;
        break;
      case 'color':
        control = renderColor(field, path, id);
        break;
      case 'select':
        control = renderSelect(field, path, id);
        break;
      default:
        control = renderText(field, path, id);
    }

    if (label) wrap.appendChild(label);
    wrap.appendChild(control);
    if (field.help) wrap.appendChild(h('p', { class: 'field__help', text: field.help }));
    if (error) wrap.appendChild(h('p', { class: 'field__error', text: error }));
    return wrap;
  }

  function renderText(field, path, id) {
    var multiline = field.type === 'textarea' || field.type === 'paragraphs';
    var value = getPath(state.content, path);
    var input = h(multiline ? 'textarea' : 'input', {
      class: 'input',
      id: id,
      rows: multiline ? (field.rows || 3) : null,
      type: multiline ? null : ({ url: 'url', embed: 'url', digits: 'text', phone: 'tel' }[field.type] || 'text'),
      inputmode: field.type === 'digits' ? 'numeric' : null,
      placeholder: { url: 'https://', embed: 'https://www.youtube.com/embed/…', link: 'e.g. donate, #section or https://…' }[field.type] || null
    });
    if (field.type === 'url' || field.type === 'embed' || field.type === 'link') input.spellcheck = false;
    input.value = value == null ? '' : String(value);

    var counter = field.max ? h('span', { class: 'field__count' }) : null;
    function update() {
      if (!counter) return;
      var len = input.value.length;
      counter.textContent = len + ' / ' + field.max;
      counter.classList.toggle('is-over', len > field.max);
      counter.hidden = len < field.max * 0.8;   // only shown when getting close to the limit
    }
    input.addEventListener('input', function () {
      setPath(state.content, path, input.value);
      update();
      changed();
    });
    update();
    return counter ? h('div', { class: 'field__control' }, [input, counter]) : input;
  }

  /** Colour picker + hex text box, kept in sync */
  function renderColor(field, path, id) {
    var value = /^#[0-9a-f]{6}$/i.test(getPath(state.content, path) || '') ? getPath(state.content, path) : (field.default || '#000000');
    var picker = h('input', { type: 'color', class: 'color__picker', value: value, 'aria-label': field.label + ' (picker)' });
    var hex = h('input', { type: 'text', class: 'input color__hex', id: id, value: value, maxlength: 7, spellcheck: null });
    hex.spellcheck = false;
    picker.addEventListener('input', function () {
      hex.value = picker.value.toUpperCase();
      setPath(state.content, path, hex.value);
      changed();
    });
    hex.addEventListener('input', function () {
      var v = hex.value.trim();
      if (/^#[0-9a-f]{6}$/i.test(v)) picker.value = v;
      setPath(state.content, path, v);
      changed();
    });
    var reset = field.default ? h('button', {
      type: 'button', class: 'btn btn--link btn--sm color__reset', text: 'Reset',
      onclick: function () { picker.value = field.default; hex.value = field.default; setPath(state.content, path, field.default); changed(); }
    }) : null;
    return h('div', { class: 'color' }, [picker, hex, reset]);
  }

  function renderSelect(field, path, id) {
    var current = getPath(state.content, path);
    var keys = Object.keys(field.options || {});
    if (keys.length && keys.indexOf(current) === -1) {     // new item / missing value: start on the first option
      current = keys[0];
      setPath(state.content, path, current);
    }
    var select = h('select', { class: 'input', id: id });
    Object.keys(field.options || {}).forEach(function (key) {
      select.appendChild(h('option', { value: key, text: field.options[key], selected: key === current }));
    });
    select.addEventListener('change', function () { setPath(state.content, path, select.value); changed(); });
    return select;
  }

  function renderMedia(field, path, id) {
    var kind = field.type;
    var accept = kind === 'image'
      ? '.jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml'
      : (field.accept === 'webm' ? '.webm,video/webm' : (field.accept === 'mp4' ? '.mp4,video/mp4' : '.mp4,.webm,video/mp4,video/webm'));

    var box = h('div', { class: 'media' });
    var preview = h('div', { class: 'media__preview' });
    var name = h('span', { class: 'media__name' });
    var progress = h('div', { class: 'progress', hidden: true }, [h('span', { class: 'progress__bar' })]);
    var fileInput = h('input', { type: 'file', accept: accept, class: 'sr-only', id: id, 'aria-labelledby': id + '-label' });
    var chooseBtn = h('button', { type: 'button', class: 'btn btn--ghost btn--sm', text: 'Upload new…', onclick: function () { fileInput.click(); } });
    var removeBtn = field.required ? null : h('button', {
      type: 'button', class: 'btn btn--link btn--sm', text: 'Remove',
      onclick: function () { setPath(state.content, path, ''); draw(); changed(); }
    });

    function draw() {
      var value = getPath(state.content, path) || '';
      preview.textContent = '';
      if (value) {
        preview.appendChild(kind === 'image'
          ? h('img', { src: mediaUrl(value), alt: '' })
          : h('video', { src: mediaUrl(value), muted: true, controls: true, preload: 'metadata' }));
      } else {
        preview.appendChild(h('span', { class: 'media__empty', text: kind === 'image' ? 'No image' : 'No video' }));
      }
      name.textContent = value ? value.split('/').pop() : '';
      if (removeBtn) removeBtn.hidden = !value;
    }

    fileInput.addEventListener('change', function () {
      var file = fileInput.files[0];
      fileInput.value = '';
      if (!file) return;
      state.uploading++;
      refreshSaveState();
      chooseBtn.disabled = true;
      progress.hidden = false;
      var bar = progress.firstChild;
      bar.style.width = '0%';
      upload(file, kind, function (p) { bar.style.width = Math.round(p * 100) + '%'; }).then(function (res) {
        state.uploading--;
        chooseBtn.disabled = false;
        progress.hidden = true;
        if (res.ok) {
          setPath(state.content, path, res.path);
          delete state.errors[path];
          draw();
          toast('Uploaded. Click “Save changes” to publish it.', 'success');
        } else {
          toast(res.message || 'Upload failed.', 'error');
        }
        changed();
      });
    });

    draw();
    box.appendChild(preview);
    box.appendChild(h('div', { class: 'media__side' }, [
      name,
      h('div', { class: 'media__buttons' }, [chooseBtn, removeBtn]),
      progress,
      h('p', { class: 'field__help', text: kind === 'image' ? 'JPG, PNG, WebP or SVG' : 'MP4 or WebM' }),
      fileInput
    ]));
    return box;
  }

  function renderList(field, path) {
    var error = state.errors[path];
    var wrap = h('div', { class: 'list' + (error ? ' has-error' : ''), 'data-path': path });
    var fixed = field.fixed != null;
    var min = fixed ? field.fixed : (field.min || 0);
    var max = fixed ? field.fixed : (field.max || 50);

    function items() {
      var arr = getPath(state.content, path);
      if (!Array.isArray(arr)) { arr = []; setPath(state.content, path, arr); }
      return arr;
    }

    function blank() {
      var item = {};
      field.fields.forEach(function (f) {
        item[f.key] = f.type === 'toggle' ? !!f.default
          : (f.type === 'select' ? Object.keys(f.options || {})[0] || '' : '');
      });
      return item;
    }

    /** Summary title of an item: the title field (option name for a select), "hidden" when switched off */
    function itemTitle(item) {
      var tf = field.fields.filter(function (f) { return f.key === field.titleField; })[0];
      var v = item[field.titleField];
      var title = tf && tf.type === 'select' && tf.options ? (tf.options[v] || '') : (v ? String(v).split('\n')[0] : '');
      if (item.block === 'custom' && item.title) title += ' — ' + String(item.title).split('\n')[0];
      return title + (item.show === false ? '  (hidden)' : '');
    }

    /** Sub-fields with showIf: [siblingKey, value] are only shown when the sibling matches */
    function applyShowIf(body, item) {
      Array.prototype.forEach.call(body.querySelectorAll('[data-show-if]'), function (row) {
        var rule = row.getAttribute('data-show-if').split('=');
        row.hidden = String(item[rule[0]]) !== rule[1];
      });
    }

    function draw(openIndex) {
      wrap.textContent = '';
      var arr = items();
      if (error) wrap.appendChild(h('p', { class: 'field__error', text: error }));

      if (fixed && field.fields.length === 1) {
        var sub = field.fields[0];
        arr.forEach(function (item, i) {
          var row = renderField(Object.assign({}, sub, { label: field.itemLabel + ' ' + (i + 1) }), joinPath(joinPath(path, i), sub.key));
          row.classList.add('field--row');
          wrap.appendChild(row);
        });
        return;
      }

      arr.forEach(function (item, i) {
        var itemPath = joinPath(path, i);
        var hasError = Object.keys(state.errors).some(function (p) { return p.indexOf(itemPath + '.') === 0; });
        var title = itemTitle(item);
        var body = h('div', { class: 'list__body' });
        field.fields.forEach(function (sub) {
          var row = renderField(sub, joinPath(itemPath, sub.key));
          if (sub.showIf) row.setAttribute('data-show-if', sub.showIf[0] + '=' + sub.showIf[1]);
          body.appendChild(row);
        });
        applyShowIf(body, item);

        var tools = fixed ? null : h('div', { class: 'list__tools' }, [
          h('button', { type: 'button', class: 'icon-btn', title: 'Move up', 'aria-label': 'Move up', text: '↑', disabled: i === 0,
            onclick: function (e) { e.preventDefault(); move(i, -1); } }),
          h('button', { type: 'button', class: 'icon-btn', title: 'Move down', 'aria-label': 'Move down', text: '↓', disabled: i === arr.length - 1,
            onclick: function (e) { e.preventDefault(); move(i, 1); } }),
          h('button', { type: 'button', class: 'icon-btn icon-btn--danger', title: 'Delete', 'aria-label': 'Delete ' + field.itemLabel, text: '✕', disabled: arr.length <= min,
            onclick: function (e) { e.preventDefault(); remove(i, title); } })
        ]);

        var titleEl = h('span', { class: 'list__title', text: title });
        var details;
        var refresh = function () {
          applyShowIf(body, item);
          titleEl.textContent = itemTitle(item);
          details.classList.toggle('is-off', item.show === false);
        };
        body.addEventListener('change', refresh);
        body.addEventListener('input', refresh);
        wrap.appendChild(details = h('details', { class: 'list__item' + (hasError ? ' has-error' : '') + (item.show === false ? ' is-off' : ''), open: fixed || hasError || i === openIndex || arr.length === 1 }, [
          h('summary', { class: 'list__summary' }, [
            h('span', { class: 'list__label', text: field.itemLabel + ' ' + (i + 1) }),
            titleEl,
            tools
          ]),
          body
        ]));
      });

      if (!fixed) {
        wrap.appendChild(h('button', {
          type: 'button', class: 'btn btn--ghost btn--sm list__add', disabled: arr.length >= max,
          text: '+ Add ' + field.itemLabel.toLowerCase(),
          onclick: function () { items().push(blank()); draw(items().length - 1); changed(); }
        }));
      }
    }

    function move(i, dir) {
      var arr = items();
      var j = i + dir;
      if (j < 0 || j >= arr.length) return;
      var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp;
      state.errors = {};       // positions changed; errors will be re-checked on save
      draw(j);
      changed();
    }

    function remove(i, title) {
      if (!confirm('Delete ' + field.itemLabel.toLowerCase() + (title ? ' “' + title + '”' : ' ' + (i + 1)) + '?')) return;
      items().splice(i, 1);
      state.errors = {};
      draw();
      changed();
    }

    draw();
    return h('div', { class: 'field field--list' }, [h('span', { class: 'field__label', text: field.label }), wrap]);
  }

  /* =================================================================
     Change password dialog
     ================================================================= */

  function setupPasswordDialog() {
    var dialog = document.getElementById('password-dialog');
    var form = document.getElementById('password-form');
    var message = form.querySelector('.form-message');

    document.getElementById('password-open').addEventListener('click', function () {
      form.reset();
      message.textContent = '';
      dialog.showModal();
      document.querySelector('.menu').open = false;
    });
    form.querySelector('[data-close]').addEventListener('click', function () { dialog.close(); });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = Object.fromEntries(new FormData(form));
      postJson('password', data).then(function (res) {
        if (res.ok) {
          dialog.close();
          toast('Password changed.', 'success');
        } else {
          message.textContent = res.message || 'Could not change the password.';
        }
      });
    });
  }

  /* =================================================================
     Start
     ================================================================= */

  function start() {
    request('api.php?action=load').then(function (res) {
      if (!res.ok) {
        editor.textContent = '';
        editor.appendChild(h('p', { class: 'alert alert--error', text: res.message || 'Could not load the content.' }));
        return;
      }
      state.schema = res.schema;
      state.content = clone(res.content || {});
      state.savedSnapshot = JSON.stringify(state.content);

      renderSection((location.hash || '').slice(1));
      refreshSaveState();
    });

    window.addEventListener('hashchange', function () { renderSection(location.hash.slice(1)); });
    window.addEventListener('beforeunload', warnUnsaved);
    saveBtn.addEventListener('click', save);
    document.addEventListener('keydown', function (e) {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(); }
    });
    setupPasswordDialog();
  }

  start();
})();

<?php
/* =====================================================================
   CONTENT SCHEMA — the single definition of everything the admin can edit.
   The admin form is generated from it, and saved content is validated
   against it (unknown keys are dropped).

   Section  → shown in the admin sidebar
   Group    → a card; 'path' is where its fields live in content.json
   Field    → key, label, type, optional: help, max, required, pattern, rows
   Types    : text · textarea · paragraphs · url · embed · digits · phone
              link (page, page#part, #part, https://, mailto:, tel:)
              toggle (optional 'default') · color · select (with 'options')
              image · video · list (with 'fields', min/max or fixed)
   List sub-fields may have 'showIf' => [siblingKey, value]: the admin only
   shows them when that sibling has that value (e.g. custom page sections).

   Page layouts: a list of {block, show} under "<page>.layout" decides which
   sections a page shows and in what order (see data-layout in js/script.js).
   ===================================================================== */

declare(strict_types=1);

/** Icons available for cards (names match the icon set in js/components.js) */
$icons = [
    'book' => 'Book (education)', 'medical' => 'Medical cross', 'bowl' => 'Food bowl',
    'sprout' => 'Leaves (livelihoods)', 'people' => 'People (community)', 'skill' => 'Person + gear (skills)',
    'handshake' => 'Handshake', 'hearthand' => 'Heart in hand', 'heart' => 'Heart', 'shield' => 'Shield',
    'eye' => 'Eye (vision)', 'lotus' => 'Lotus', 'diya' => 'Diya', 'check' => 'Tick',
    'coins' => 'Coins (money)', 'school' => 'School', 'alert' => 'Warning sign', 'scale' => 'Scales (legal)',
    'heartpulse' => 'Heart + pulse (health)', 'bookopen' => 'Open book', 'temple' => 'Temple', 'globe' => 'Globe',
    'info' => 'Info', 'calendar' => 'Calendar', 'phone' => 'Phone', 'mail' => 'Email', 'pin' => 'Location pin',
    'snan' => 'Water + drop (snan)', 'pandit' => 'Pandit', 'namaste' => 'Folded hands', 'flame' => 'Fire (havan)',
    'doc' => 'Document (details)', 'edit' => 'Pen',
    'touch' => 'Hand (choose)', 'gear' => 'Gear (arrange)', 'chart' => 'Growth chart',
];
$iconField  = ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => $icons];
$colorField = ['key' => 'color', 'label' => 'Icon colour', 'type' => 'color', 'default' => '#E8590C'];
$showField  = ['key' => 'show', 'label' => 'Show on the website', 'type' => 'toggle', 'default' => true];
$linkHelp   = 'A page of this website (e.g. donate or donate#donate-form), a section of the same page (e.g. #anti-conversion), or a full link starting with https://';
$link = static fn (string $key, string $label, bool $required = false): array =>
    ['key' => $key, 'label' => $label, 'type' => 'link', 'max' => 300, 'required' => $required, 'help' => $linkHelp];
$seoFields = [
    ['key' => 'title', 'label' => 'SEO title', 'type' => 'text', 'max' => 90, 'required' => true, 'help' => 'Shown in browser tabs and search results. Aim for about 50–60 characters.'],
    ['key' => 'description', 'label' => 'Meta description', 'type' => 'textarea', 'rows' => 3, 'max' => 300, 'required' => true, 'help' => 'Short search-result summary. Aim for about 150–160 characters.'],
    ['key' => 'keywords', 'label' => 'Focus keywords (your notes)', 'type' => 'text', 'max' => 250, 'help' => 'Comma-separated phrases to guide page copy. Search engines do not use meta keywords for ranking.'],
    ['key' => 'canonical', 'label' => 'Canonical URL', 'type' => 'url', 'max' => 500, 'required' => true, 'help' => 'The preferred clean URL for this page. Keep the site URL and avoid .html.'],
    ['key' => 'index', 'label' => 'Allow search engines to index this page', 'type' => 'toggle', 'default' => true],
    ['key' => 'follow', 'label' => 'Allow search engines to follow links', 'type' => 'toggle', 'default' => true],
    ['key' => 'ogTitle', 'label' => 'Social sharing title', 'type' => 'text', 'max' => 90, 'help' => 'Optional; leave empty to use the SEO title.'],
    ['key' => 'ogDescription', 'label' => 'Social sharing description', 'type' => 'textarea', 'rows' => 2, 'max' => 300, 'help' => 'Optional; leave empty to use the meta description.'],
    ['key' => 'ogImage', 'label' => 'Social sharing image', 'type' => 'image', 'help' => 'Optional image shown when this page is shared. Use a wide image where possible.'],
];
$seoPages = [
    'home' => 'Home page',
    'about' => 'About Us',
    'how' => 'How It Works',
    'faith' => 'Protect Our Faith',
    'donate' => 'Donate',
    'event' => 'Event Information',
    'booking' => 'Service / Booking',
    'contact' => 'Contact Us',
    'services' => 'Services',
    'jyotirlinga' => '12 Jyotirlinga',
    'privacy' => 'Privacy Policy',
    'terms' => 'Terms & Conditions',
];

/** Icons for the Services pages: the common set plus the extra ones drawn by js/services.js */
$serviceIcons = $icons + [
    'cart' => 'Cart (store)', 'video' => 'Video', 'camera' => 'Camera', 'edit' => 'Pen (sankalp)',
];
$serviceIconField = ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => $serviceIcons];
$accentHelp = 'Shown in orange straight after the heading. Leave empty for a one-colour heading.';

/** "Page layout" list: which sections a page shows, in which order */
$layout = static fn (array $blocks, array $extra = []): array =>
    ['key' => 'layout', 'label' => 'Sections (top to bottom)', 'type' => 'list', 'min' => 1, 'max' => 20,
     'itemLabel' => 'Section', 'titleField' => 'block', 'fields' => array_merge([
        ['key' => 'block', 'label' => 'Section', 'type' => 'select', 'options' => $blocks],
        ['key' => 'show', 'label' => 'Show this section', 'type' => 'toggle', 'default' => true],
    ], $extra)];

return [
    [
        'id' => 'home', 'title' => 'Home page', 'icon' => 'home',
        'groups' => [
            ['path' => 'home', 'title' => 'Hero slider (top of the page)', 'help' => 'The slides at the top of the Home page. Each slide has its own photo, heading, text and two buttons. They change every 5 seconds, in this order.', 'fields' => [
                ['key' => 'heroSlides', 'label' => 'Slides', 'type' => 'list', 'min' => 1, 'max' => 6, 'itemLabel' => 'Slide', 'titleField' => 'title', 'fields' => [
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140, 'required' => true, 'help' => 'Put *stars* around the words that should get the gold shine, e.g. Your Faith, Your *Spiritual Connection.*'],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                    ['key' => 'primaryText', 'label' => 'Orange button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                    $link('primaryLink', 'Orange button link'),
                    ['key' => 'secondaryText', 'label' => 'White button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                    $link('secondaryLink', 'White button link'),
                    ['key' => 'image', 'label' => 'Slide photo', 'type' => 'image', 'required' => true, 'help' => 'Wide landscape photo, at least 1800 px wide, about 2 : 1. Keep the person or main subject on the right side; the left side sits behind the text.'],
                    ['key' => 'imageAlt', 'label' => 'Photo description (for accessibility)', 'type' => 'text', 'max' => 200],
                ]],
            ]],
            ['path' => 'home', 'title' => 'Trust points under the buttons', 'fields' => [
                ['key' => 'trust', 'label' => 'Points', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Point', 'titleField' => 'label', 'fields' => [
                    ['key' => 'label', 'label' => 'Text', 'type' => 'text', 'max' => 40],
                ]],
            ]],
            ['path' => 'home.intro', 'title' => 'Introduction (text + four photo cards)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 7, 'max' => 1800, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'cards', 'label' => 'Photo cards (right side)', 'type' => 'list', 'min' => 1, 'max' => 6, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 60, 'required' => true],
                    ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'help' => 'Landscape photo, about 4 : 3.'],
                    ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                    $link('link', 'Where the card leads'),
                ]],
            ]],
            ['path' => 'home.process', 'title' => 'Our Work Process (steps in a row)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 100],
                ['key' => 'steps', 'label' => 'Steps', 'type' => 'list', 'min' => 1, 'max' => 6, 'itemLabel' => 'Step', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'number', 'label' => 'Number', 'type' => 'text', 'max' => 4, 'help' => 'e.g. 01'],
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 60, 'required' => true],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'max' => 400],
                ]],
            ]],
            ['path' => 'home.reasons', 'title' => 'Reason to Choose Our Service (two columns)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 100],
                ['key' => 'leftItems', 'label' => 'Left column', 'type' => 'list', 'min' => 0, 'max' => 5, 'itemLabel' => 'Block', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'max' => 400],
                ]],
                ['key' => 'rightItems', 'label' => 'Right column', 'type' => 'list', 'min' => 0, 'max' => 5, 'itemLabel' => 'Block', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'max' => 400],
                ]],
                ['key' => 'decorImage', 'label' => 'Faded temple picture — left edge', 'type' => 'image', 'help' => 'Tall picture that fades into the background. Remove to show none.'],
            ]],
            ['path' => 'home.purpose', 'title' => 'Our Purpose (2 × 2 blocks)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 100],
                ['key' => 'items', 'label' => 'Blocks', 'type' => 'list', 'min' => 1, 'max' => 8, 'itemLabel' => 'Block', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'number', 'label' => 'Number', 'type' => 'text', 'max' => 4, 'help' => 'e.g. 01'],
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'max' => 500],
                ]],
            ]],
            ['path' => 'home.bottomCta', 'title' => 'Banner above the footer', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'primaryText', 'label' => 'Orange button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                $link('primaryLink', 'Orange button link'),
                ['key' => 'secondaryText', 'label' => 'Light button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                $link('secondaryLink', 'Light button link'),
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'help' => 'Very wide photo (about 6 : 1). A light wash is laid over it so the text stays readable.'],
            ]],
        ],
    ],
    [
        'id' => 'faith', 'title' => 'Protect Our Faith', 'icon' => 'faith',
        'groups' => [
            ['path' => 'faith', 'title' => 'Page layout', 'help' => 'Choose which sections the page shows and their order (↑ ↓ to move, the switch to hide). The section numbers 01, 02 … follow this order automatically. “Custom section” adds a new text + photo section that you fill in right here.', 'fields' => [
                $layout([
                    'hero' => 'Hero (top of the page)', 'heritage' => 'Why Our Roots Matter', 'issue' => 'What Is Anti-Conversion?',
                    'vulnerability' => 'Why People Become Vulnerable (cards)', 'response' => 'How Can We Address It (cards)',
                    'support' => 'Your Support Can Make a Difference', 'respect' => 'Respect Every Person', 'cta' => 'Closing banner',
                    'custom' => 'Custom section (text + photo)',
                ], [
                    ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 60, 'showIf' => ['block', 'custom']],
                    ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 160, 'showIf' => ['block', 'custom']],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 5, 'max' => 3000, 'showIf' => ['block', 'custom'], 'help' => 'Leave an empty line between paragraphs.'],
                    ['key' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text', 'max' => 40, 'showIf' => ['block', 'custom']],
                    ['key' => 'buttonLink', 'label' => 'Button link', 'type' => 'link', 'max' => 300, 'showIf' => ['block', 'custom'], 'help' => $linkHelp],
                    ['key' => 'image', 'label' => 'Photo (optional)', 'type' => 'image', 'showIf' => ['block', 'custom']],
                    ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200, 'showIf' => ['block', 'custom']],
                    ['key' => 'imageSide', 'label' => 'Photo position', 'type' => 'select', 'options' => ['right' => 'Right of the text', 'left' => 'Left of the text'], 'showIf' => ['block', 'custom']],
                    ['key' => 'style', 'label' => 'Background', 'type' => 'select', 'options' => ['plain' => 'Light (cream)', 'tinted' => 'Soft blue-grey band'], 'showIf' => ['block', 'custom']],
                ]),
            ]],
            ['path' => 'faith.hero', 'title' => 'Hero (top of the page)', 'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small orange line above the heading', 'type' => 'text', 'max' => 90],
                ['key' => 'title', 'label' => 'Main heading (white part)', 'type' => 'textarea', 'rows' => 2, 'max' => 120, 'required' => true, 'help' => 'Press Enter to start a new line.'],
                ['key' => 'titleAccent', 'label' => 'Main heading (orange ending)', 'type' => 'text', 'max' => 80, 'help' => 'Shown in orange straight after the white part. Leave empty for an all-white heading.'],
                ['key' => 'subtitle', 'label' => 'Orange sub-heading', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 4, 'max' => 1200, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'primaryText', 'label' => 'Orange button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                $link('primaryLink', 'Orange button link'),
                ['key' => 'secondaryText', 'label' => 'White button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                $link('secondaryLink', 'White button link'),
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true, 'help' => 'Landscape photo, ideally 1600 px wide or more. It fills the right side; the left side fades into dark navy behind the text.'],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'faith.heritage', 'title' => '“Why Our Roots Matter”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 5, 'max' => 2000, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'callout', 'label' => 'Highlighted statement (orange box)', 'type' => 'textarea', 'rows' => 2, 'max' => 200, 'help' => 'Leave empty to hide the box.'],
                ['key' => 'calloutIcon', 'label' => 'Icon in the orange box', 'type' => 'select', 'options' => $icons],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true, 'help' => 'Landscape photo (about 2 : 1).'],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'faith.issue', 'title' => '“What Is Anti-Conversion?” (blue-grey band)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 5, 'max' => 2000],
                ['key' => 'cardIcon', 'label' => 'Card icon', 'type' => 'select', 'options' => $icons],
                ['key' => 'cardTitle', 'label' => 'Card heading', 'type' => 'text', 'max' => 80, 'help' => 'Leave empty to hide the white card.'],
                ['key' => 'cardText', 'label' => 'Card text', 'type' => 'textarea', 'rows' => 4, 'max' => 800],
            ]],
            ['path' => 'faith.vulnerability', 'title' => '“Why Might People Become Vulnerable?” cards', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'max' => 600],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'list', 'min' => 1, 'max' => 12, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 80, 'required' => true],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 300],
                    ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true, 'help' => 'Wide photo (about 3 : 1).'],
                    ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                ]],
            ]],
            ['path' => 'faith.response', 'title' => '“How Can We Address Coercive Conversion?” cards', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Intro text (optional)', 'type' => 'textarea', 'rows' => 3, 'max' => 600],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'list', 'min' => 1, 'max' => 12, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                    $showField, $iconField,
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 80, 'required' => true],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 300],
                ]],
                ['key' => 'decorLeft', 'label' => 'Faded temple picture — left edge', 'type' => 'image', 'help' => 'Tall picture; fades into the background. Remove to show none.'],
                ['key' => 'decorRight', 'label' => 'Faded temple picture — right edge', 'type' => 'image'],
            ]],
            ['path' => 'faith.support', 'title' => '“Your Support Can Make a Real Difference”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 4, 'max' => 1500],
                ['key' => 'buttonText', 'label' => 'Button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                $link('buttonLink', 'Button link'),
                ['key' => 'note', 'label' => 'Small note under the button', 'type' => 'textarea', 'rows' => 2, 'max' => 250],
                ['key' => 'listTitle', 'label' => 'List box heading', 'type' => 'text', 'max' => 80, 'help' => 'Leave empty to hide the list box.'],
                ['key' => 'list', 'label' => 'Ticked list', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Item', 'titleField' => 'text', 'fields' => [
                    $showField,
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'max' => 160, 'required' => true],
                ]],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'help' => 'Roughly square photo. Remove to hide it.'],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'faith.respect', 'title' => '“Protect Our Faith. Respect Every Person.”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label (after the number)', 'type' => 'text', 'max' => 60],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 140],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 7, 'max' => 3000, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true, 'help' => 'Wide landscape photo.'],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'faith.cta', 'title' => 'Closing banner (dark)', 'fields' => [
                ['key' => 'title', 'label' => 'Heading (white part)', 'type' => 'text', 'max' => 100],
                ['key' => 'titleAccent', 'label' => 'Heading (gold ending)', 'type' => 'text', 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty for no button (as in the design).'],
                $link('buttonLink', 'Button link'),
                ['key' => 'tagline', 'label' => 'Gold line at the bottom', 'type' => 'text', 'max' => 100],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true, 'help' => 'Very wide, dark photo.'],
            ]],
        ],
    ],
    [
        'id' => 'donate', 'title' => 'Donate', 'icon' => 'donate',
        'groups' => [
            ['path' => 'donate.hero', 'title' => 'Donation hero', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'text', 'max' => 60, 'required' => true],
                ['key' => 'titleAccent', 'label' => 'Heading accent', 'type' => 'text', 'max' => 40],
                ['key' => 'subtitle', 'label' => 'Subheading', 'type' => 'text', 'max' => 100],
                ['key' => 'text', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'backgroundImage', 'label' => 'Temple background photo', 'type' => 'image', 'required' => true],
                ['key' => 'offeringImage', 'label' => 'Offering photo', 'type' => 'image', 'required' => true],
                ['key' => 'offeringAlt', 'label' => 'Offering photo description', 'type' => 'text', 'max' => 200],
                ['key' => 'promises', 'label' => 'Commitment points', 'type' => 'list', 'min' => 1, 'max' => 6, 'itemLabel' => 'Commitment', 'titleField' => 'text', 'fields' => [
                    $iconField,
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'max' => 40, 'required' => true],
                ]],
            ]],
            ['path' => 'donate.impact', 'title' => 'Impact section', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading first line', 'type' => 'text', 'max' => 60],
                ['key' => 'titlePrefix', 'label' => 'Heading second-line start', 'type' => 'text', 'max' => 40],
                ['key' => 'titleAccent', 'label' => 'Heading highlighted ending', 'type' => 'text', 'max' => 50],
                ['key' => 'text', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                ['key' => 'items', 'label' => 'Impact list', 'type' => 'list', 'min' => 1, 'max' => 10, 'itemLabel' => 'Item', 'titleField' => 'title', 'fields' => [
                    $iconField, $colorField,
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 50, 'required' => true],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                ]],
                ['key' => 'script', 'label' => 'Handwritten line', 'type' => 'textarea', 'rows' => 2, 'max' => 100],
            ]],
            ['path' => 'donate.form', 'title' => 'Donation form', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'max' => 600],
                ['key' => 'amountsLabel', 'label' => '“Choose Amount” label', 'type' => 'text', 'max' => 40],
                ['key' => 'amounts', 'label' => 'Suggested amounts (₹)', 'type' => 'list', 'min' => 1, 'max' => 8, 'itemLabel' => 'Amount', 'titleField' => 'value', 'fields' => [
                    ['key' => 'value', 'label' => 'Amount in ₹ (digits only)', 'type' => 'digits', 'max' => 8, 'required' => true],
                ]],
                ['key' => 'otherLabel', 'label' => '“Other Amount” button', 'type' => 'text', 'max' => 30],
                ['key' => 'methodsLabel', 'label' => '“Payment Method” label', 'type' => 'text', 'max' => 40],
                ['key' => 'button', 'label' => 'Donate button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'secureNote', 'label' => 'Note under the button', 'type' => 'text', 'max' => 120],
            ]],
            ['path' => 'donate.form', 'title' => 'UPI payment details', 'help' => 'Donors pay directly to this UPI account. Upload the QR code from your bank / UPI app.', 'fields' => [
                ['key' => 'upiId', 'label' => 'UPI ID', 'type' => 'text', 'max' => 80, 'help' => 'e.g. yourname@okaxis'],
                ['key' => 'upiName', 'label' => 'Payee name (shown in the UPI app)', 'type' => 'text', 'max' => 80],
                ['key' => 'upiQr', 'label' => 'UPI QR code image', 'type' => 'image'],
                ['key' => 'upiTitle', 'label' => '“Scan & Pay” title', 'type' => 'text', 'max' => 40],
                ['key' => 'upiText', 'label' => '“Scan & Pay” text', 'type' => 'text', 'max' => 100],
                ['key' => 'otherMethodNote', 'label' => 'Message for Card / Net Banking / Wallet', 'type' => 'textarea', 'rows' => 3, 'max' => 400, 'help' => 'Shown until a payment gateway is connected.'],
            ]],
            ['path' => 'donate.form', 'title' => 'After the donor submits', 'fields' => [
                ['key' => 'successTitle', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'successText', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
            ]],
            ['path' => 'donate.thanks', 'title' => 'Thank-you banner', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'script', 'label' => 'Handwritten line', 'type' => 'text', 'max' => 100],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true],
            ]],
        ],
    ],
    [
        'id' => 'events', 'title' => 'Events', 'icon' => 'calendar',
        'groups' => [
            ['path' => '', 'title' => 'Sacred events', 'help' => 'The first event is shown on the Home and Event Information pages. All events appear in the booking form.', 'fields' => [
                ['key' => 'events', 'label' => 'Events', 'type' => 'list', 'min' => 1, 'max' => 10, 'itemLabel' => 'Event', 'titleField' => 'name', 'fields' => [
                    ['key' => 'name', 'label' => 'Event name', 'type' => 'text', 'max' => 120, 'required' => true],
                    ['key' => 'location', 'label' => 'Location', 'type' => 'text', 'max' => 160],
                    ['key' => 'dates', 'label' => 'Dates', 'type' => 'text', 'max' => 120],
                    ['key' => 'shortDescription', 'label' => 'Short description (Home page)', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                    ['key' => 'significance', 'label' => 'Significance', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                    ['key' => 'about', 'label' => 'About the event (Event page)', 'type' => 'paragraphs', 'rows' => 6, 'max' => 6000, 'help' => 'Leave an empty line between paragraphs.'],
                    ['key' => 'image', 'label' => 'Event photo', 'type' => 'image', 'required' => true],
                    ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                ]],
            ]],
            ['path' => 'eventPage', 'title' => 'Event Information page', 'fields' => [
                ['key' => 'label', 'label' => 'Small label above the event name', 'type' => 'text', 'max' => 60],
            ]],
            ['path' => 'eventPage.banner', 'title' => 'Event page banner', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                ['key' => 'text', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
                ['key' => 'image', 'label' => 'Banner photo', 'type' => 'image', 'required' => true],
            ]],
        ],
    ],
    [
        'id' => 'about', 'title' => 'About Us', 'icon' => 'info',
        'groups' => [
            ['path' => 'about.heroBanner', 'title' => 'Banner (top of the page)', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120, 'required' => true],
                ['key' => 'text', 'label' => 'Supporting text', 'type' => 'textarea', 'rows' => 3, 'max' => 250],
                ['key' => 'buttonText', 'label' => 'Button text', 'type' => 'text', 'max' => 30, 'help' => 'Leave empty to hide the button.'],
                $link('buttonLink', 'Button link'),
                ['key' => 'image', 'label' => 'Banner photo', 'type' => 'image', 'required' => true, 'help' => 'Very wide photo (about 4 : 1 shows best). Keep the main subject on the right; the text sits on the left.'],
                ['key' => 'imageAlt', 'label' => 'Photo description (for accessibility)', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'about.purpose', 'title' => '“Our Purpose” section', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 7, 'max' => 4000, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'about', 'title' => 'Value cards', 'fields' => [
                ['key' => 'values', 'label' => 'Values', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Value', 'titleField' => 'title', 'fields' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 40],
                ]],
            ]],
        ],
    ],
    [
        'id' => 'how', 'title' => 'How It Works', 'icon' => 'steps',
        'groups' => [
            ['path' => 'how.intro', 'title' => 'Page heading', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => '', 'title' => 'The four steps', 'help' => 'Shown on the Home page and the How It Works page.', 'fields' => [
                ['key' => 'steps', 'label' => 'Steps', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Step', 'titleField' => 'title', 'fields' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 40],
                    ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                ]],
            ]],
            ['path' => 'how.provide', 'title' => '“What You Need to Provide”', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'items', 'label' => 'Items', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Item', 'titleField' => 'label', 'fields' => [
                    ['key' => 'label', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'max' => 60],
                    ['key' => 'note', 'label' => 'Small note (optional)', 'type' => 'text', 'max' => 40],
                ]],
            ]],
            ['path' => 'how.faq', 'title' => 'FAQ', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'items', 'label' => 'Questions', 'type' => 'list', 'min' => 0, 'max' => 30, 'itemLabel' => 'Question', 'titleField' => 'q', 'fields' => [
                    ['key' => 'q', 'label' => 'Question', 'type' => 'text', 'max' => 200, 'required' => true],
                    ['key' => 'a', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 3, 'max' => 2000, 'required' => true],
                ]],
            ]],
        ],
    ],
    [
        'id' => 'services', 'title' => 'Services', 'icon' => 'steps',
        'groups' => [
            ['path' => 'services.banner', 'title' => 'Services page banner', 'help' => 'The banner at the top of the Services page (the page with all service cards).', 'fields' => [
                ['key' => 'title', 'label' => 'Heading (navy part)', 'type' => 'text', 'max' => 60],
                ['key' => 'titleAccent', 'label' => 'Heading (orange part)', 'type' => 'text', 'max' => 60, 'help' => $accentHelp],
                ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                ['key' => 'image', 'label' => 'Banner photo', 'type' => 'image', 'help' => 'Wide landscape photo; the left side fades into the cream background behind the text.'],
            ]],
            ['path' => 'services.process', 'title' => 'Process heading on every service page', 'help' => 'The four process steps themselves are shared with the Home page and are edited under “How It Works”.', 'fields' => [
                ['key' => 'title', 'label' => 'Heading (navy part)', 'type' => 'text', 'max' => 60],
                ['key' => 'titleAccent', 'label' => 'Heading (orange part)', 'type' => 'text', 'max' => 60, 'help' => $accentHelp],
                ['key' => 'subtitle', 'label' => 'Line under the heading', 'type' => 'text', 'max' => 200, 'help' => 'Write {service} where the name of the service should appear.'],
            ]],
            ['path' => 'services', 'title' => 'Services', 'help' => 'Each service has a card on the Services page and its own detail page. The list order is the order of the cards, and the card number (Service 01, 02 …) follows it. To change the order, type a new position in the number box next to a service and press Enter, or drag the service by its header. Sections you leave empty are not shown on the detail page.', 'fields' => [
                ['key' => 'knownDefaults', 'label' => 'Default services already seen', 'type' => 'hidden'],
                ['key' => 'items', 'label' => 'Services', 'type' => 'list', 'min' => 0, 'max' => 40, 'itemLabel' => 'Service', 'titleField' => 'title', 'fields' => [
                    $showField,
                    ['key' => 'status', 'label' => 'Detail page status', 'type' => 'select', 'options' => ['draft' => 'Draft (detail page not ready yet)', 'published' => 'Published'], 'help' => 'While a service is a draft, its “View Details” button opens the link below instead of the detail page.'],
                    ['key' => 'design', 'label' => 'Detail page design', 'type' => 'select', 'options' => ['standard' => 'Standard', 'v2' => 'New (banner text + button, Why Choose in 4 columns, Who Can Use beside FAQs, rounded bottom banner)']],
                    ['key' => 'slug', 'label' => 'Page address (slug)', 'type' => 'slug', 'max' => 80, 'required' => true, 'help' => 'Small letters, numbers and dashes, e.g. virtual-punya-snan. The page opens at /service?name=virtual-punya-snan. Must be different for every service.'],
                    $serviceIconField,
                    ['key' => 'title', 'label' => 'Service name', 'type' => 'text', 'max' => 90, 'required' => true],
                    ['key' => 'text', 'label' => 'Short description (card)', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                    ['key' => 'image', 'label' => 'Card photo', 'type' => 'image'],
                    ['key' => 'imageAlt', 'label' => 'Card photo description', 'type' => 'text', 'max' => 200],
                    $link('link', '“View Details” link while the page is a draft'),

                    ['key' => 'bannerTitle', 'label' => 'Detail page — banner heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120, 'help' => 'Press Enter to start a new line. Empty = the service name. Used when no banner slides are added below.'],
                    ['key' => 'bannerAccent', 'label' => 'Banner — words shown in orange', 'type' => 'text', 'max' => 80, 'help' => 'Must be written exactly as in the heading, e.g. Punya Snan.'],
                    ['key' => 'bannerSlides', 'label' => 'Banner — sliding text (up to 3)', 'type' => 'list', 'min' => 0, 'max' => 3, 'itemLabel' => 'Slide', 'titleField' => 'title', 'help' => 'The banner photo stays the same; these texts change by themselves every 4 seconds. Slide 1 is the main page heading. Leave empty to show only the heading above.', 'fields' => [
                        ['key' => 'label', 'label' => 'Small orange label (optional)', 'type' => 'text', 'max' => 50, 'help' => 'e.g. Our Service'],
                        ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120, 'required' => true, 'help' => 'Press Enter to start a new line.'],
                        ['key' => 'accent', 'label' => 'Words shown in orange (optional)', 'type' => 'text', 'max' => 80, 'help' => 'Must be written exactly as in the heading.'],
                        ['key' => 'text', 'label' => 'Line under the heading (optional)', 'type' => 'textarea', 'rows' => 2, 'max' => 220],
                        ['key' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty for no button.'],
                        $link('buttonLink', 'Button link'),
                    ]],
                    ['key' => 'bannerImage', 'label' => 'Banner photo', 'type' => 'image', 'help' => 'Best size 2400 × 900 px. Keep the main subject on the right half; the left side fades behind the text.'],
                    ['key' => 'bannerImageAlt', 'label' => 'Banner photo description', 'type' => 'text', 'max' => 200],

                    ['key' => 'aboutLabel', 'label' => 'About — small orange label', 'type' => 'text', 'max' => 60],
                    ['key' => 'aboutTitle', 'label' => 'About — heading', 'type' => 'text', 'max' => 120],
                    ['key' => 'aboutText1', 'label' => 'About — paragraph 1', 'type' => 'textarea', 'rows' => 4, 'max' => 1200],
                    ['key' => 'aboutText2', 'label' => 'About — paragraph 2', 'type' => 'textarea', 'rows' => 4, 'max' => 1200],
                    ['key' => 'aboutImage', 'label' => 'About — photo (right side)', 'type' => 'image'],
                    ['key' => 'aboutImageAlt', 'label' => 'About — photo description', 'type' => 'text', 'max' => 200],
                    ['key' => 'showShloka', 'label' => 'Show the Sanskrit shloka', 'type' => 'toggle', 'default' => false],
                    ['key' => 'shloka', 'label' => 'Sanskrit shloka', 'type' => 'textarea', 'rows' => 3, 'max' => 600, 'help' => 'Press Enter between the lines.'],

                    ['key' => 'processTitle', 'label' => 'Process — heading (this service only)', 'type' => 'text', 'max' => 80, 'help' => 'Empty = the shared heading (Our Process).'],
                    ['key' => 'processSubtitle', 'label' => 'Process — line under the heading (this service only)', 'type' => 'text', 'max' => 200, 'help' => 'Empty = the shared line set above.'],
                    ['key' => 'processSteps', 'label' => 'Process — steps for this service only', 'type' => 'list', 'min' => 0, 'max' => 4, 'itemLabel' => 'Step', 'titleField' => 'title', 'help' => 'Leave empty to show the shared four steps (edited under “How It Works”).', 'fields' => [
                        $serviceIconField,
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 60, 'required' => true],
                        ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'max' => 240],
                    ]],

                    ['key' => 'receiveTitle', 'label' => 'What You Receive — heading', 'type' => 'text', 'max' => 60],
                    ['key' => 'receiveAccent', 'label' => 'What You Receive — orange part', 'type' => 'text', 'max' => 60, 'help' => $accentHelp],
                    ['key' => 'receiveSubtitle', 'label' => 'What You Receive — line under the heading', 'type' => 'text', 'max' => 160],
                    ['key' => 'receive', 'label' => 'What You Receive — cards', 'type' => 'list', 'min' => 0, 'max' => 8, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                        $serviceIconField,
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 60, 'required' => true],
                        ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                        ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'help' => 'Wide photo (about 2.5 : 1).'],
                        ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                    ]],

                    ['key' => 'whyTitle', 'label' => 'Why Choose — heading', 'type' => 'text', 'max' => 80],
                    ['key' => 'whyAccent', 'label' => 'Why Choose — orange part', 'type' => 'text', 'max' => 80, 'help' => $accentHelp],
                    ['key' => 'why', 'label' => 'Why Choose — benefits', 'type' => 'list', 'min' => 0, 'max' => 8, 'itemLabel' => 'Benefit', 'titleField' => 'title', 'fields' => [
                        $serviceIconField,
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 80, 'required' => true],
                        ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                    ]],

                    ['key' => 'whoTitle', 'label' => 'Who Can Use — heading', 'type' => 'text', 'max' => 60],
                    ['key' => 'whoAccent', 'label' => 'Who Can Use — orange part', 'type' => 'text', 'max' => 60, 'help' => $accentHelp],
                    ['key' => 'whoIntro', 'label' => 'Who Can Use — intro text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                    ['key' => 'whoList', 'label' => 'Who Can Use — list', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Point', 'titleField' => 'text', 'fields' => [
                        ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'max' => 200, 'required' => true],
                    ]],
                    ['key' => 'whoClosing', 'label' => 'Who Can Use — closing text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],

                    ['key' => 'showPlaces', 'label' => 'Show the scrolling cards section (places, festivals, pujas …)', 'type' => 'toggle', 'default' => false],
                    ['key' => 'placesTitle', 'label' => 'Scrolling cards — heading', 'type' => 'text', 'max' => 60, 'help' => 'e.g. Sacred Places or Major Indian Festivals.'],
                    ['key' => 'placesSubtitle', 'label' => 'Scrolling cards — line under the heading', 'type' => 'text', 'max' => 200],
                    ['key' => 'placesIntro', 'label' => 'Scrolling cards — intro text (optional)', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                    ['key' => 'places', 'label' => 'Scrolling cards', 'type' => 'list', 'min' => 0, 'max' => 20, 'itemLabel' => 'Card', 'titleField' => 'name', 'fields' => [
                        ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'max' => 80, 'required' => true],
                        ['key' => 'location', 'label' => 'Location (city · river, or city, state)', 'type' => 'text', 'max' => 120],
                        ['key' => 'text', 'label' => 'Short description (optional)', 'type' => 'textarea', 'rows' => 3, 'max' => 300],
                        ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'help' => 'Landscape photo (about 3 : 2).'],
                        ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                        ['key' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text', 'max' => 40, 'help' => 'e.g. Explore Details. Leave empty for no button.'],
                        $link('buttonLink', 'Button link'),
                    ]],
                    ['key' => 'placesNote', 'label' => 'Scrolling cards — small note under the cards (optional)', 'type' => 'text', 'max' => 160],

                    ['key' => 'faqTitle', 'label' => 'FAQ — heading', 'type' => 'text', 'max' => 80],
                    ['key' => 'faqAccent', 'label' => 'FAQ — orange part', 'type' => 'text', 'max' => 40, 'help' => $accentHelp],
                    ['key' => 'faqs', 'label' => 'FAQ — questions', 'type' => 'list', 'min' => 0, 'max' => 30, 'itemLabel' => 'Question', 'titleField' => 'q', 'fields' => [
                        ['key' => 'q', 'label' => 'Question', 'type' => 'text', 'max' => 200, 'required' => true],
                        ['key' => 'a', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 3, 'max' => 2000, 'required' => true],
                    ]],

                    ['key' => 'ctaTitle', 'label' => 'Bottom banner — heading', 'type' => 'text', 'max' => 100, 'help' => 'Leave empty to hide the bottom banner.'],
                    ['key' => 'ctaText', 'label' => 'Bottom banner — text', 'type' => 'text', 'max' => 200],
                    ['key' => 'ctaButton', 'label' => 'Bottom banner — button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                    $link('ctaLink', 'Bottom banner — button link'),
                    ['key' => 'ctaImage', 'label' => 'Bottom banner — photo', 'type' => 'image', 'help' => 'Very wide photo; the left side fades behind the text.'],

                    ['key' => 'seoTitle', 'label' => 'SEO title (browser tab, Google)', 'type' => 'text', 'max' => 90, 'help' => 'Empty = “service name | Hindu.International”.'],
                    ['key' => 'seoDescription', 'label' => 'SEO description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                ]],
            ]],
        ],
    ],
    [
        'id' => 'jyotirlinga', 'title' => '12 Jyotirlinga pages', 'icon' => 'steps',
        'groups' => [
            ['path' => 'jyotirlingas', 'title' => 'Jyotirlinga pages', 'help' => 'Each Jyotirlinga has its own page at /jyotirlinga?name=slug (for example /jyotirlinga?name=somnath). To open it from the 12 Jyotirlinga service page, set that temple card’s button link (Services → 12 Jyotirlinga → Scrolling cards) to jyotirlinga?name=slug. Sections you leave empty are not shown. Keep the photo sizes below the same on every page.', 'fields' => [
                ['key' => 'knownDefaults', 'label' => 'Default pages already seen', 'type' => 'hidden'],
                ['key' => 'items', 'label' => 'Jyotirlingas', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Jyotirlinga', 'titleField' => 'name', 'fields' => [
                    ['key' => 'show', 'label' => 'Page is live on the website', 'type' => 'toggle', 'default' => true],
                    ['key' => 'slug', 'label' => 'Page address (slug)', 'type' => 'slug', 'max' => 60, 'required' => true, 'help' => 'Small letters and dashes, e.g. somnath. The page opens at /jyotirlinga?name=somnath.'],
                    ['key' => 'name', 'label' => 'Temple name', 'type' => 'text', 'max' => 80, 'required' => true, 'help' => 'e.g. Somnath Jyotirlinga. Used in the enquiry message and to link the matching card on the 12 Jyotirlinga page.'],

                    ['key' => 'heroTitle', 'label' => 'Top banner — heading', 'type' => 'textarea', 'rows' => 2, 'max' => 80, 'help' => 'Press Enter to start a new line. Empty = the temple name.'],
                    ['key' => 'heroSubtitle', 'label' => 'Top banner — line under the heading', 'type' => 'text', 'max' => 100],
                    ['key' => 'heroText', 'label' => 'Top banner — description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                    ['key' => 'heroButton', 'label' => 'Top banner — button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                    $link('heroLink', 'Top banner — button link (contact = enquiry form with the temple name filled in)'),
                    ['key' => 'heroImage', 'label' => 'Top banner — photo', 'type' => 'image', 'help' => 'Size 2400 × 1000 px (12 : 5). Temple on the right half; the left side fades into the sky colour behind the text.'],
                    ['key' => 'heroImageAlt', 'label' => 'Top banner — photo description', 'type' => 'text', 'max' => 200],

                    ['key' => 'aboutTitle', 'label' => 'About — heading', 'type' => 'text', 'max' => 100],
                    ['key' => 'aboutText1', 'label' => 'About — paragraph 1', 'type' => 'textarea', 'rows' => 4, 'max' => 1200],
                    ['key' => 'aboutText2', 'label' => 'About — paragraph 2', 'type' => 'textarea', 'rows' => 4, 'max' => 1200],
                    ['key' => 'aboutImage', 'label' => 'About — photo (right side)', 'type' => 'image', 'help' => 'Size 1400 × 1000 px (7 : 5).'],
                    ['key' => 'aboutImageAlt', 'label' => 'About — photo description', 'type' => 'text', 'max' => 200],

                    ['key' => 'sevaTitle', 'label' => 'Darshan & Puja Seva — heading', 'type' => 'text', 'max' => 100],
                    ['key' => 'sevaText', 'label' => 'Darshan & Puja Seva — line under the heading', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                    ['key' => 'seva', 'label' => 'Darshan & Puja Seva — cards', 'type' => 'list', 'min' => 0, 'max' => 4, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                        $iconField,
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 50, 'required' => true],
                        ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                    ]],
                    ['key' => 'sevaImage', 'label' => 'Darshan & Puja Seva — photo (right edge)', 'type' => 'image', 'help' => 'Size 1200 × 1100 px (12 : 11). Its left side fades into the page.'],
                    ['key' => 'sevaImageAlt', 'label' => 'Darshan & Puja Seva — photo description', 'type' => 'text', 'max' => 200],

                    ['key' => 'visitTitle', 'label' => 'Temple Visit — heading', 'type' => 'text', 'max' => 100],
                    ['key' => 'location', 'label' => 'Temple Visit — location', 'type' => 'text', 'max' => 160],
                    ['key' => 'timings', 'label' => 'Temple Visit — timings', 'type' => 'text', 'max' => 120],
                    ['key' => 'timingsNote', 'label' => 'Temple Visit — small note under the timings', 'type' => 'text', 'max' => 120],
                    ['key' => 'reach', 'label' => 'Temple Visit — how to reach', 'type' => 'textarea', 'rows' => 3, 'max' => 400, 'help' => 'Press Enter to start a new line.'],
                    ['key' => 'visitImage', 'label' => 'Temple Visit — photo', 'type' => 'image', 'help' => 'Size 1600 × 750 px (32 : 15).'],
                    ['key' => 'visitImageAlt', 'label' => 'Temple Visit — photo description', 'type' => 'text', 'max' => 200],

                    ['key' => 'faqTitle', 'label' => 'FAQ — heading', 'type' => 'text', 'max' => 80],
                    ['key' => 'faqs', 'label' => 'FAQ — questions', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Question', 'titleField' => 'q', 'fields' => [
                        ['key' => 'q', 'label' => 'Question', 'type' => 'text', 'max' => 200, 'required' => true],
                        ['key' => 'a', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 3, 'max' => 1200, 'required' => true],
                    ]],

                    ['key' => 'ctaTitle', 'label' => 'Bottom banner — heading (white)', 'type' => 'text', 'max' => 100, 'help' => 'Leave this and the gold line empty to hide the bottom banner.'],
                    ['key' => 'ctaAccent', 'label' => 'Bottom banner — gold line', 'type' => 'text', 'max' => 80],
                    ['key' => 'ctaText', 'label' => 'Bottom banner — text', 'type' => 'text', 'max' => 200],
                    ['key' => 'ctaButton', 'label' => 'Bottom banner — button text', 'type' => 'text', 'max' => 40, 'help' => 'Leave empty to hide the button.'],
                    $link('ctaLink', 'Bottom banner — button link'),
                    ['key' => 'ctaImage', 'label' => 'Bottom banner — photo (left side)', 'type' => 'image', 'help' => 'Size 1600 × 800 px (2 : 1). Subject on the left; the right side fades into navy.'],

                    ['key' => 'seoTitle', 'label' => 'SEO title (browser tab, Google)', 'type' => 'text', 'max' => 90, 'help' => 'Empty = “temple name | Hindu.International”.'],
                    ['key' => 'seoDescription', 'label' => 'SEO description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                ]],
            ]],
        ],
    ],
    [
        'id' => 'booking', 'title' => 'Service / Booking', 'icon' => 'form',
        'groups' => [
            ['path' => 'booking', 'title' => 'Booking page', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
                ['key' => 'infoTitle', 'label' => 'Information box heading', 'type' => 'text', 'max' => 60],
                ['key' => 'info', 'label' => 'Information points', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Point', 'titleField' => 'text', 'fields' => [
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'max' => 160],
                ]],
                ['key' => 'image', 'label' => 'Photo in the information box', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
        ],
    ],
    [
        'id' => 'contact', 'title' => 'Contact', 'icon' => 'phone',
        'groups' => [
            ['path' => 'contact', 'title' => 'Contact details', 'help' => 'Used on the Contact page, the footer and the WhatsApp buttons.', 'fields' => [
                ['key' => 'phoneDisplay', 'label' => 'Phone number (as shown on the site)', 'type' => 'text', 'max' => 40],
                ['key' => 'phoneLink', 'label' => 'Phone number for the call link', 'type' => 'phone', 'max' => 20, 'help' => 'With country code, e.g. +919876543210'],
                ['key' => 'whatsappNumber', 'label' => 'WhatsApp number', 'type' => 'digits', 'max' => 15, 'help' => 'Digits only, with country code, e.g. 919876543210'],
                ['key' => 'whatsappMessage', 'label' => 'WhatsApp greeting message', 'type' => 'text', 'max' => 200],
                ['key' => 'email', 'label' => 'Email address', 'type' => 'text', 'max' => 120],
                ['key' => 'hours', 'label' => 'Working days', 'type' => 'text', 'max' => 60],
                ['key' => 'hoursTime', 'label' => 'Working hours', 'type' => 'text', 'max' => 60],
            ]],
            ['path' => 'contactPage.banner', 'title' => 'Contact page banner', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                ['key' => 'text', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
                ['key' => 'image', 'label' => 'Banner photo', 'type' => 'image', 'required' => true],
            ]],
            ['path' => 'contactPage', 'title' => 'Contact page text', 'fields' => [
                ['key' => 'infoTitle', 'label' => 'Contact box heading', 'type' => 'text', 'max' => 60],
                ['key' => 'infoText', 'label' => 'Contact box text', 'type' => 'text', 'max' => 200],
                ['key' => 'formTitle', 'label' => 'Form heading', 'type' => 'text', 'max' => 60],
                ['key' => 'formText', 'label' => 'Form text', 'type' => 'text', 'max' => 200],
            ]],
        ],
    ],
    [
        'id' => 'media', 'title' => 'Social & video', 'icon' => 'video',
        'groups' => [
            ['path' => 'social', 'title' => 'Social media links', 'help' => 'Full link starting with https://. Leave empty to hide the icon.', 'fields' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'url'],
                ['key' => 'x', 'label' => 'X', 'type' => 'url'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'url'],
            ]],
            ['path' => 'media', 'title' => 'Videos', 'fields' => [
                ['key' => 'heroVideoMp4', 'label' => 'Home hero background video (MP4)', 'type' => 'video', 'accept' => 'mp4', 'help' => 'Short (8–15 s), no sound needed, under ~10 MB. Replaces the hero photo when set.'],
                ['key' => 'heroVideoWebm', 'label' => 'Same video as WebM (optional)', 'type' => 'video', 'accept' => 'webm'],
                ['key' => 'howItWorksVideo', 'label' => '“Watch How It Works” video link', 'type' => 'embed', 'help' => 'YouTube embed link, e.g. https://www.youtube.com/embed/VIDEO_ID. Empty = button opens the How It Works page.'],
            ]],
        ],
    ],
    [
        'id' => 'site', 'title' => 'Header, footer & colours', 'icon' => 'palette',
        'groups' => [
            ['path' => 'site', 'title' => 'Logo', 'fields' => [
                ['key' => 'logoText', 'label' => 'Logo text', 'type' => 'text', 'max' => 40, 'required' => true, 'help' => 'The first “.” is shown in orange, e.g. Hindu.International'],
                ['key' => 'logoTagline', 'label' => 'Small line under the logo text', 'type' => 'text', 'max' => 60, 'help' => 'Leave empty to hide it.'],
                ['key' => 'logoFontFamily', 'label' => 'Brand name font style', 'type' => 'select', 'options' => ['serif' => 'Classic serif', 'sans' => 'Clean sans-serif']],
                ['key' => 'logoFontSize', 'label' => 'Brand name font size', 'type' => 'select', 'options' => ['20' => 'Small (20 px)', '22' => '22 px', '24' => '24 px', '26' => '26 px', '28' => '28 px', '30' => 'Large (30 px)']],
                ['key' => 'logoBold', 'label' => 'Bold brand name', 'type' => 'toggle', 'default' => true],
                ['key' => 'logoSize', 'label' => 'Logo size', 'type' => 'select', 'options' => ['md' => 'Medium', 'lg' => 'Large (recommended)', 'xl' => 'Extra large']],
                ['key' => 'logoImage', 'label' => 'Logo mark image (optional)', 'type' => 'image', 'help' => 'Use a symbol-only PNG/SVG with a transparent background. The brand name and tagline are shown as editable text beside it.'],
                ['key' => 'footerLogoImage', 'label' => 'Logo image for the dark footer (optional)', 'type' => 'image', 'help' => 'A light/white version of the logo for the dark footer. Without it the footer uses the header logo image (or the lotus + text).'],
            ]],
            ['path' => 'site', 'title' => 'Header menu', 'help' => 'Shown in the header and in the footer “Quick Links”. Use ↑ ↓ to reorder, the switch to hide an item.', 'fields' => [
                ['key' => 'nav', 'label' => 'Menu items', 'type' => 'list', 'min' => 1, 'max' => 10, 'itemLabel' => 'Menu item', 'titleField' => 'label', 'fields' => [
                    $showField,
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'max' => 30, 'required' => true],
                    $link('link', 'Link', true),
                ]],
                ['key' => 'headerCta', 'label' => 'Header button text', 'type' => 'text', 'max' => 30, 'help' => 'The orange button on the right of the menu. Leave empty to hide it.'],
                $link('headerCtaLink', 'Header button link'),
            ]],
            ['path' => 'site', 'title' => 'Footer', 'fields' => [
                ['key' => 'footerTagline', 'label' => 'Footer tagline', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                ['key' => 'footerQuickTitle', 'label' => 'Heading above the menu links', 'type' => 'text', 'max' => 40],
                ['key' => 'footerLinksTitle', 'label' => 'Heading of the second link column', 'type' => 'text', 'max' => 40],
                ['key' => 'footerLinks', 'label' => 'Second link column', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Link', 'titleField' => 'label', 'fields' => [
                    $showField,
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'max' => 40, 'required' => true],
                    $link('link', 'Link', true),
                ]],
                ['key' => 'copyright', 'label' => 'Text after “© year Logo text.”', 'type' => 'text', 'max' => 120],
            ]],
            ['path' => 'theme', 'title' => 'Colours', 'help' => 'Changes the colours across the whole website.', 'fields' => [
                ['key' => 'primary', 'label' => 'Main colour (buttons, highlights)', 'type' => 'color', 'default' => '#E85A0C'],
                ['key' => 'primaryDark', 'label' => 'Main colour — darker shade', 'type' => 'color', 'default' => '#C94708'],
                ['key' => 'gold', 'label' => 'Gold accent', 'type' => 'color', 'default' => '#D99A35'],
                ['key' => 'navy', 'label' => 'Navy (headings on Protect Our Faith + Donate pages, logo)', 'type' => 'color', 'default' => '#16396B'],
                ['key' => 'background', 'label' => 'Page background (cream)', 'type' => 'color', 'default' => '#FFF8ED'],
                ['key' => 'footer', 'label' => 'Footer background', 'type' => 'color', 'default' => '#111C28'],
            ]],
        ],
    ],
    [
        'id' => 'seo', 'title' => 'On-page SEO', 'icon' => 'search',
        'groups' => array_map(
            static fn (string $page, string $title): array => [
                'path' => 'seo.pages.' . $page,
                'title' => $title,
                'help' => 'Edit search-result metadata, social sharing preview, canonical URL and indexing for this page.',
                'fields' => $seoFields,
            ],
            array_keys($seoPages),
            array_values($seoPages)
        ),
    ],
];

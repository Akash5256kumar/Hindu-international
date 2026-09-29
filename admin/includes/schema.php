<?php
/* =====================================================================
   CONTENT SCHEMA — the single definition of everything the admin can edit.
   The admin form is generated from it, and saved content is validated
   against it (unknown keys are dropped).

   Section  → shown in the admin sidebar
   Group    → a card; 'path' is where its fields live in content.json
   Field    → key, label, type, optional: help, max, required, pattern, rows
   Types    : text · textarea · paragraphs · url · embed · digits · phone
              toggle · image · video · list (with 'fields', min/max or fixed)
   ===================================================================== */

declare(strict_types=1);

return [
    [
        'id' => 'home', 'title' => 'Home page', 'icon' => 'home',
        'groups' => [
            ['path' => 'home.hero', 'title' => 'Hero (top of the page)', 'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small line above the heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120, 'help' => 'Press Enter to start a new line.'],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'textarea', 'rows' => 2, 'max' => 60, 'required' => true],
                ['key' => 'text', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true, 'help' => 'Wide landscape photo, at least 1920 px wide. The person should be on the right side.'],
                ['key' => 'imageAlt', 'label' => 'Photo description (for accessibility)', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'home', 'title' => 'Trust points under the buttons', 'fields' => [
                ['key' => 'trust', 'label' => 'Points', 'type' => 'list', 'fixed' => 4, 'itemLabel' => 'Point', 'titleField' => 'label', 'fields' => [
                    ['key' => 'label', 'label' => 'Text', 'type' => 'text', 'max' => 40],
                ]],
            ]],
            ['path' => 'home.service', 'title' => '“Our Service” section', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 80],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'max' => 600],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'home.how', 'title' => '“How It Works” section heading', 'help' => 'The four steps are edited under “How It Works”.', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'home.event', 'title' => '“Upcoming Event” section heading', 'help' => 'The event itself is edited under “Events”.', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 40],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'home.cta', 'title' => 'Banner above the footer', 'fields' => [
                ['key' => 'quote', 'label' => 'Quote', 'type' => 'text', 'max' => 100],
                ['key' => 'text', 'label' => 'Line under the quote', 'type' => 'text', 'max' => 160],
                ['key' => 'button', 'label' => 'Button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true, 'help' => 'Very wide photo; the left side stays visible, the right side sits behind the text.'],
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
            ['path' => 'about.banner', 'title' => 'Banner', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80, 'required' => true],
                ['key' => 'text', 'label' => 'Subtitle', 'type' => 'text', 'max' => 200],
                ['key' => 'image', 'label' => 'Banner photo', 'type' => 'image', 'required' => true],
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
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'url'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'url'],
                ['key' => 'showWhatsapp', 'label' => 'Show WhatsApp icon in the footer', 'type' => 'toggle'],
            ]],
            ['path' => 'media', 'title' => 'Videos', 'fields' => [
                ['key' => 'heroVideoMp4', 'label' => 'Home hero background video (MP4)', 'type' => 'video', 'accept' => 'mp4', 'help' => 'Short (8–15 s), no sound needed, under ~10 MB. Replaces the hero photo when set.'],
                ['key' => 'heroVideoWebm', 'label' => 'Same video as WebM (optional)', 'type' => 'video', 'accept' => 'webm'],
                ['key' => 'howItWorksVideo', 'label' => '“Watch How It Works” video link', 'type' => 'embed', 'help' => 'YouTube embed link, e.g. https://www.youtube.com/embed/VIDEO_ID. Empty = button opens the How It Works page.'],
            ]],
        ],
    ],
];

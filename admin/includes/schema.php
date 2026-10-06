<?php
/* =====================================================================
   CONTENT SCHEMA — the single definition of everything the admin can edit.
   The admin form is generated from it, and saved content is validated
   against it (unknown keys are dropped).

   Section  → shown in the admin sidebar
   Group    → a card; 'path' is where its fields live in content.json
   Field    → key, label, type, optional: help, max, required, pattern, rows
   Types    : text · textarea · paragraphs · url · embed · digits · phone
              toggle · color · select (with 'options') · image · video
              list (with 'fields', min/max or fixed)
   ===================================================================== */

declare(strict_types=1);

/** Icons available for cards (names match the icon set in js/components.js) */
$icons = [
    'book' => 'Book (education)', 'medical' => 'Medical cross', 'bowl' => 'Food bowl',
    'sprout' => 'Leaves (livelihoods)', 'people' => 'People (community)', 'skill' => 'Person + gear (skills)',
    'handshake' => 'Handshake', 'hearthand' => 'Heart in hand', 'heart' => 'Heart', 'shield' => 'Shield',
    'eye' => 'Eye (vision)', 'lotus' => 'Lotus', 'diya' => 'Diya', 'check' => 'Tick',
];
$iconField  = ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => $icons];
$colorField = ['key' => 'color', 'label' => 'Icon colour', 'type' => 'color', 'default' => '#E8590C'];

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
        'id' => 'roots', 'title' => 'Preserve Our Roots', 'icon' => 'roots',
        'groups' => [
            ['path' => 'roots.hero', 'title' => 'Hero (top of the page)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'textarea', 'rows' => 3, 'max' => 160, 'required' => true, 'help' => 'Press Enter to start a new line.'],
                ['key' => 'subtitle', 'label' => 'Orange sub-heading', 'type' => 'textarea', 'rows' => 2, 'max' => 160],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 6, 'max' => 1500, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'primaryButton', 'label' => 'Orange button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'secondaryButton', 'label' => 'White button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true, 'help' => 'Wide landscape photo; keep the people on the right side.'],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'roots.why', 'title' => '“Why This Matters”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 7, 'max' => 2000, 'help' => 'Leave an empty line between paragraphs.'],
                ['key' => 'callout', 'label' => 'Highlighted statement (orange box)', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                ['key' => 'image', 'label' => 'Photo (portrait)', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'roots.approach', 'title' => '“Our Approach” card', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 60],
                ['key' => 'lead', 'label' => 'Bold first line', 'type' => 'textarea', 'rows' => 2, 'max' => 160],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 5, 'max' => 1200],
                ['key' => 'items', 'label' => 'Ticked list', 'type' => 'list', 'min' => 0, 'max' => 12, 'itemLabel' => 'Item', 'titleField' => 'text', 'fields' => [
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'max' => 80, 'required' => true],
                ]],
                ['key' => 'closing', 'label' => 'Closing line', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'roots.anti', 'title' => '“What Is Anti-Conversion?” (blue band)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'paragraphs', 'rows' => 5, 'max' => 1500],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'roots.focus', 'title' => '“What We Focus On” cards', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'items', 'label' => 'Cards', 'type' => 'list', 'min' => 1, 'max' => 10, 'itemLabel' => 'Card', 'titleField' => 'title', 'fields' => [
                    $iconField, $colorField,
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'max' => 50, 'required' => true],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2, 'max' => 200],
                    ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true],
                    ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                ]],
            ]],
            ['path' => 'roots.people', 'title' => '“Our Roots Are People”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'max' => 800],
                ['key' => 'highlight', 'label' => 'Bold statement', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'roots.vision', 'title' => '“Our Vision” card', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 60],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 4, 'max' => 800],
                ['key' => 'highlight', 'label' => 'Orange statement', 'type' => 'textarea', 'rows' => 2, 'max' => 160],
            ]],
            ['path' => 'roots.change', 'title' => '“Real Change” section', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'max' => 80],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'button', 'label' => 'Button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'link', 'label' => 'Link text', 'type' => 'text', 'max' => 60],
                ['key' => 'image', 'label' => 'Photo (portrait)', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
                ['key' => 'tiles', 'label' => 'Icon tiles', 'type' => 'list', 'min' => 1, 'max' => 6, 'itemLabel' => 'Tile', 'titleField' => 'label', 'fields' => [
                    $iconField, $colorField,
                    ['key' => 'label', 'label' => 'Label', 'type' => 'textarea', 'rows' => 2, 'max' => 50, 'required' => true],
                ]],
            ]],
            ['path' => 'roots.choice', 'title' => '“Your Donation Creates Choice” (blue card)', 'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'button', 'label' => 'Button text', 'type' => 'text', 'max' => 40, 'required' => true],
            ]],
            ['path' => 'roots.cta', 'title' => 'Dark banner at the bottom', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 160],
                ['key' => 'button', 'label' => 'Button text', 'type' => 'text', 'max' => 40, 'required' => true],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true],
            ]],
        ],
    ],
    [
        'id' => 'donate', 'title' => 'Donate', 'icon' => 'donate',
        'groups' => [
            ['path' => 'donate.hero', 'title' => 'Hero (top of the page)', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Main heading', 'type' => 'textarea', 'rows' => 2, 'max' => 100, 'required' => true],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 500],
                ['key' => 'image', 'label' => 'Background photo', 'type' => 'image', 'required' => true],
                ['key' => 'imageAlt', 'label' => 'Photo description', 'type' => 'text', 'max' => 200],
            ]],
            ['path' => 'donate.impact', 'title' => '“How Your Donation Helps”', 'fields' => [
                ['key' => 'label', 'label' => 'Small label', 'type' => 'text', 'max' => 50],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'textarea', 'rows' => 2, 'max' => 100],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3, 'max' => 400],
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
            ['path' => 'site', 'title' => 'Header & footer', 'fields' => [
                ['key' => 'logoText', 'label' => 'Logo text', 'type' => 'text', 'max' => 40, 'required' => true, 'help' => 'The first “.” is shown in orange, e.g. Hindu.International'],
                ['key' => 'logoImage', 'label' => 'Logo image', 'type' => 'image', 'help' => 'Upload a JPG, PNG, WebP or SVG logo. Leave empty to use the lotus icon and logo text.'],
                ['key' => 'headerCta', 'label' => 'Header button text', 'type' => 'text', 'max' => 30, 'required' => true],
                ['key' => 'footerTagline', 'label' => 'Footer tagline', 'type' => 'textarea', 'rows' => 2, 'max' => 120],
                ['key' => 'nav', 'label' => 'Menu labels', 'type' => 'list', 'fixed' => 7, 'itemLabel' => 'Menu item', 'titleField' => 'label', 'fields' => [
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'max' => 30, 'required' => true],
                ]],
            ]],
            ['path' => 'theme', 'title' => 'Colours', 'help' => 'Changes the colours across the whole website.', 'fields' => [
                ['key' => 'primary', 'label' => 'Main colour (buttons, highlights)', 'type' => 'color', 'default' => '#E85A0C'],
                ['key' => 'primaryDark', 'label' => 'Main colour — darker shade', 'type' => 'color', 'default' => '#C94708'],
                ['key' => 'gold', 'label' => 'Gold accent', 'type' => 'color', 'default' => '#D99A35'],
                ['key' => 'navy', 'label' => 'Navy (headings on Preserve Our Roots pages, logo)', 'type' => 'color', 'default' => '#16396B'],
                ['key' => 'background', 'label' => 'Page background (cream)', 'type' => 'color', 'default' => '#FFF8ED'],
                ['key' => 'footer', 'label' => 'Footer background', 'type' => 'color', 'default' => '#111C28'],
            ]],
        ],
    ],
];

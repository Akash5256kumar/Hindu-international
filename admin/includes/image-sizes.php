<?php
/* =====================================================================
   Upload size shown under every image box in the admin.
   Measured from how big each photo is drawn on the live pages (1920 px and
   1440 px wide screens and a 390 px phone). Small photos are listed at twice
   their on-screen size so they stay sharp on high-resolution screens.
   Key = content path (* = any item in a list); value = [size, ratio, note].
   An empty size means the image is not shown by the current page design.
   ===================================================================== */

declare(strict_types=1);

return [
    // Home
    'home.heroSlides.*.image'       => ['1920 × 760 px', '5 : 2', 'On phones only the right part shows (almost square) — keep the main subject on the right.'],
    'home.intro.image'              => ['1200 × 800 px', '3 : 2', ''],
    'home.intro.cards.*.image'      => ['', '', 'Not shown on the current Home page design.'],
    'home.process.bgImage'          => ['1920 × 570 px', '10 : 3', 'Background. On phones the section is tall, so only a middle strip shows.'],
    'home.reasons.centerImage'      => ['600 × 1320 px', '5 : 11 (tall)', ''],
    'home.purpose.bgImage'          => ['1920 × 680 px', '≈ 2.8 : 1', 'Background. On phones only a middle strip shows. Empty = first hero slide photo.'],
    'home.bottomCta.rightImage'     => ['1920 × 640 px', '3 : 1', 'Background. On phones only a middle strip shows.'],

    // Protect Our Faith
    'faith.layout.*.image'          => ['', '', 'Not shown on the current page design.'],
    'faith.hero.image'              => ['1600 × 900 px', '16 : 9', 'Smaller laptops trim the sides a little.'],
    'faith.heritage.image'          => ['1100 × 550 px', '2 : 1', ''],
    'faith.vulnerability.items.*.image' => ['600 × 200 px', '3 : 1', ''],
    'faith.response.decorLeft'      => ['540 × 960 px', '9 : 16 (tall)', 'Hidden on phones.'],
    'faith.response.decorRight'     => ['540 × 960 px', '9 : 16 (tall)', 'Hidden on phones.'],
    'faith.support.image'           => ['540 × 960 px', '9 : 16 (tall)', 'On phones it shows as a wide 16 : 10 crop from the middle.'],
    'faith.respect.image'           => ['1200 × 675 px', '16 : 9', ''],
    'faith.cta.image'               => ['1920 × 270 px', '7 : 1', 'Background strip. Phones show a taller crop from the middle.'],

    // Donate
    'donate.hero.backgroundImage'   => ['1920 × 390 px', '≈ 5 : 1', 'Background. Phones show a taller crop from the middle.'],
    'donate.hero.offeringImage'     => ['1300 × 780 px', '5 : 3', 'Phones show a taller crop from the middle.'],
    'donate.form.upiQr'             => ['600 × 600 px', '1 : 1 (square)', 'PNG of the QR code.'],
    'donate.thanks.image'           => ['1920 × 375 px', '≈ 5 : 1', 'Background. Phones show a taller crop from the middle.'],

    // Events
    'events.*.image'                => ['900 × 1200 px', '3 : 4 (portrait)', 'On phones it shows as a wide 16 : 9 crop from the middle.'],
    'eventPage.banner.image'        => ['1920 × 340 px', '≈ 5.6 : 1', 'Background. Phones show a taller crop from the middle.'],

    // About Us
    'about.heroBanner.image'        => ['1800 × 1160 px', '≈ 3 : 2', ''],
    'about.intro.image'             => ['800 × 560 px', '10 : 7', 'On phones: 16 : 10 crop from the middle.'],
    'about.story.image'             => ['800 × 800 px', '1 : 1 (square)', 'On phones: 16 : 10 crop from the middle.'],
    'about.vision.image'            => ['640 × 600 px', '≈ 1 : 1', 'On phones: 16 : 10 crop from the middle.'],
    'about.mission.image'           => ['920 × 690 px', '4 : 3', 'On phones: 16 : 10 crop from the middle.'],
    'about.closing.image'           => ['600 × 510 px', '≈ 6 : 5', 'On phones: 16 : 10 crop from the middle.'],

    // How It Works
    'howPage.banner.image'          => ['1600 × 700 px', '16 : 7', 'Smaller laptops show it narrower (≈ 16 : 9).'],
    'howPage.banner.bgImage'        => ['1920 × 480 px', '4 : 1', 'Fills the whole banner behind the text.'],
    'howPage.sections.*.image'      => ['900 × 600 px', '3 : 2', '“Wide band” design: 1300 × 600 px (≈ 2.2 : 1). “Box” design: 700 × 500 px (7 : 5).'],
    'howPage.closing.bgImage'       => ['1920 × 340 px', '≈ 5.6 : 1', 'Background. Phones show a taller crop from the middle.'],
    'howPage.closing.leftImage'     => ['950 × 570 px', '5 : 3', ''],
    'howPage.closing.rightImage'    => ['920 × 690 px', '4 : 3', ''],
    'howPage.closing.lotusImage'    => ['210 × 120 px', '7 : 4', 'PNG with a transparent background.'],

    // Services
    'services.banner.image'         => ['1920 × 405 px', '≈ 4.7 : 1', 'Phones show a taller crop from the middle.'],
    'services.items.*.image'        => ['800 × 600 px', '4 : 3', 'Card photo on the Services page.'],
    'services.items.*.bannerImage'  => ['1800 × 600 px', '3 : 1', 'Phones show a crop from the middle (≈ 2 : 1).'],
    'services.items.*.aboutImage'   => ['1120 × 700 px', '16 : 10', ''],
    'services.items.*.receive.*.image' => ['600 × 300 px', '2 : 1', 'Standard-design pages show it a little thinner (≈ 2.4 : 1).'],
    'services.items.*.places.*.image'  => ['600 × 250 px', '12 : 5', 'Standard-design pages (e.g. Virtual Punya Snan): 600 × 400 px (3 : 2).'],
    'services.items.*.ctaImage'     => ['1500 × 600 px', '5 : 2', 'Standard-design pages: full-width strip 1920 × 420 px (≈ 4.6 : 1).'],
    'services.items.*.jaapOfferingsBgImage' => ['', '', 'Not shown on the current page design.'],

    // 12 Jyotirlinga pages
    'jyotirlingas.items.*.heroImage'  => ['1600 × 690 px', '7 : 3', 'Smaller laptops show it narrower (≈ 2 : 1).'],
    'jyotirlingas.items.*.aboutImage' => ['980 × 700 px', '7 : 5', ''],
    'jyotirlingas.items.*.sevaImage'  => ['1200 × 900 px', '4 : 3', 'Smaller laptops show it nearly square.'],
    'jyotirlingas.items.*.visitImage' => ['960 × 450 px', '≈ 2.1 : 1', ''],
    'jyotirlingas.items.*.ctaImage'   => ['1250 × 500 px', '5 : 2', ''],

    // Jaap & Paath pages
    'jaapServices.items.*.heroImage'  => ['1600 × 800 px', '2 : 1', 'Smaller laptops trim the sides (≈ 3 : 2).'],
    'jaapServices.items.*.ctaImage'   => ['1920 × 270 px', '7 : 1', 'Keep the middle plain: the heading and button sit there.'],

    // Booking, Contact
    'booking.image'                 => ['880 × 660 px', '4 : 3', ''],
    'contactPage.banner.image'      => ['1600 × 900 px', '16 : 9', 'Smaller laptops show it narrower (≈ 4 : 3).'],

    // Site
    'site.logoImage'                => ['300 × 300 px', '1 : 1 (square)', 'PNG with a transparent background.'],
    'site.footerLogoImage'          => ['300 × 300 px', '1 : 1 (square)', 'PNG with a transparent background, light colours for the dark footer.'],

    // SEO: social sharing picture (WhatsApp, Facebook, X …)
    'seo.pages.*.ogImage'           => ['1200 × 630 px', '1.91 : 1', 'Shown when a link to this page is shared.'],
];

<?php
/* =====================================================================
   FORM SETTINGS — edit before going live.
   ===================================================================== */

// Where service requests and contact messages are delivered.
const VPS_RECIPIENT = 'itisrupesh@gmail.com';

// Sender address. Must be an address on YOUR domain (create it in
// Hostinger → Emails), otherwise messages are likely to go to spam.
const VPS_FROM = 'no-reply@your-domain.com';
const VPS_FROM_NAME = 'Hindu.International Website';

// Photograph limits (keep in sync with js/script.js).
const VPS_MAX_BYTES = 5 * 1024 * 1024;   // 5 MB
const VPS_ALLOWED_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

// Minimum seconds between two submissions from the same visitor.
const VPS_THROTTLE_SECONDS = 30;

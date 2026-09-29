<?php
/* =====================================================================
   SVG SANITIZER — SVG files can contain JavaScript, so uploads are
   rebuilt from an allow-list: only known drawing elements/attributes are
   kept; scripts, event handlers (onload…), external links, <foreignObject>,
   entities and DOCTYPEs are removed or rejected.
   Defence in depth: uploads/.htaccess also serves files with a CSP that
   blocks scripts, and the site shows images through <img> tags.
   ===================================================================== */

declare(strict_types=1);

final class SvgSanitizer
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    private const ELEMENTS = [
        'svg', 'g', 'defs', 'symbol', 'use', 'title', 'desc', 'metadata', 'style',
        'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'textpath',
        'lineargradient', 'radialgradient', 'stop', 'pattern', 'clippath', 'mask', 'marker', 'image',
        'filter', 'feblend', 'fecolormatrix', 'fecomponenttransfer', 'fecomposite', 'feconvolvematrix',
        'fediffuselighting', 'fedisplacementmap', 'fedistantlight', 'fedropshadow', 'feflood',
        'fefunca', 'fefuncb', 'fefuncg', 'fefuncr', 'fegaussianblur', 'femerge', 'femergenode',
        'femorphology', 'feoffset', 'fepointlight', 'fespecularlighting', 'fespotlight', 'fetile', 'feturbulence',
    ];

    /** Wrappers that are removed while their children are kept */
    private const UNWRAP = ['a', 'switch'];

    private const ATTRIBUTES = [
        'id', 'class', 'style', 'lang', 'xml:space', 'role', 'aria-label', 'aria-hidden', 'focusable',
        'xmlns', 'xmlns:xlink', 'version', 'viewbox', 'preserveaspectratio', 'width', 'height', 'x', 'y',
        'x1', 'x2', 'y1', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'fx', 'fy', 'fr', 'd', 'points', 'pathlength',
        'transform', 'href', 'xlink:href', 'xlink:title',
        'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
        'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity', 'opacity',
        'color', 'display', 'visibility', 'overflow', 'clip-path', 'clip-rule', 'clippathunits', 'mask',
        'maskunits', 'maskcontentunits', 'filter', 'filterunits', 'primitiveunits', 'marker-start',
        'marker-mid', 'marker-end', 'markerwidth', 'markerheight', 'markerunits', 'refx', 'refy', 'orient',
        'offset', 'stop-color', 'stop-opacity', 'gradientunits', 'gradienttransform', 'spreadmethod',
        'patternunits', 'patterncontentunits', 'patterntransform',
        'font-family', 'font-size', 'font-weight', 'font-style', 'font-variant', 'text-anchor',
        'dominant-baseline', 'alignment-baseline', 'baseline-shift', 'letter-spacing', 'word-spacing',
        'text-decoration', 'dx', 'dy', 'rotate', 'textlength', 'lengthadjust', 'startoffset', 'side',
        'in', 'in2', 'result', 'mode', 'type', 'values', 'operator', 'k1', 'k2', 'k3', 'k4',
        'stddeviation', 'edgemode', 'flood-color', 'flood-opacity', 'lighting-color', 'basefrequency',
        'numoctaves', 'seed', 'stitchtiles', 'scale', 'xchannelselector', 'ychannelselector', 'radius',
        'order', 'kernelmatrix', 'divisor', 'bias', 'targetx', 'targety', 'preservealpha',
        'surfacescale', 'diffuseconstant', 'specularconstant', 'specularexponent', 'azimuth',
        'elevation', 'pointsatx', 'pointsaty', 'pointsatz', 'z', 'limitingconeangle', 'tablevalues',
        'slope', 'intercept', 'amplitude', 'exponent', 'mix-blend-mode', 'isolation', 'vector-effect',
        'shape-rendering', 'image-rendering', 'color-interpolation', 'color-interpolation-filters',
    ];

    /** Returns the cleaned SVG markup, or throws UploadException */
    public static function clean(string $svg): string
    {
        if (strlen($svg) > self::MAX_BYTES) {
            throw new UploadException('SVG file is too large (max 2 MB).');
        }
        // Entities / DOCTYPE are never needed for images and enable XXE / "billion laughs" attacks
        if (preg_match('/<!(DOCTYPE|ENTITY)/i', $svg)) {
            throw new UploadException('This SVG contains a DOCTYPE or entities, which are not allowed.');
        }

        $doc = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $loaded = $doc->loadXML($svg, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->documentElement;
        if (!$loaded || !$root || strtolower($root->localName) !== 'svg') {
            throw new UploadException('This file is not a valid SVG image.');
        }

        self::cleanNode($root);
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $doc->saveXML($root) . "\n";
    }

    private static function cleanNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $name = strtolower($child->localName);
                if (in_array($name, self::UNWRAP, true)) {
                    // keep the drawing inside a link / switch, drop the wrapper itself
                    $group = $node->ownerDocument->createElementNS('http://www.w3.org/2000/svg', 'g');
                    while ($child->firstChild) {
                        $group->appendChild($child->firstChild);
                    }
                    $node->replaceChild($group, $child);
                    $child = $group;
                } elseif (!in_array($name, self::ELEMENTS, true)) {
                    $node->removeChild($child);
                    continue;
                }
                self::cleanAttributes($child);
                if (strtolower($child->localName) === 'style' && self::unsafeCss($child->textContent)) {
                    $node->removeChild($child);
                    continue;
                }
                self::cleanNode($child);
            } elseif ($child instanceof DOMText) {
                continue;   // text/CDATA inside allowed elements is harmless
            } else {
                $node->removeChild($child);   // comments, processing instructions, etc.
            }
        }
        if ($node instanceof DOMElement) {
            self::cleanAttributes($node);
        }
    }

    private static function cleanAttributes(DOMElement $el): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            /** @var DOMAttr $attr */
            $name = strtolower($attr->nodeName);
            $value = $attr->value;
            $keep = in_array($name, self::ATTRIBUTES, true);

            if ($keep && ($name === 'href' || $name === 'xlink:href')) {
                // Only internal references (#id) or embedded raster images
                $v = preg_replace('/\s+/', '', $value) ?? '';
                $keep = str_starts_with($v, '#') || preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $v);
            }
            if ($keep && $name === 'style') {
                $keep = !self::unsafeCss($value);
            }
            if ($keep && preg_match('/url\s*\(/i', $value) && !preg_match('/^\s*url\(\s*[\'"]?#[\w\-:.]+[\'"]?\s*\)\s*$/i', $value) && $name !== 'style') {
                $keep = false;   // presentation attributes may only reference internal ids, e.g. fill="url(#grad)"
            }
            if (!$keep) {
                $el->removeAttributeNode($attr);
            }
        }
        // xmlns:xlink etc. are namespace nodes, not attributes, and are kept automatically
    }

    private static function unsafeCss(string $css): bool
    {
        return (bool) preg_match(
            '/@import|expression\s*\(|javascript:|vbscript:|behavior\s*:|-moz-binding|url\s*\(\s*[\'"]?\s*(?!#)/i',
            $css
        );
    }
}

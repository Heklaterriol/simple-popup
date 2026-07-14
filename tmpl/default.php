<?php

/**
 * @package     Simple Popup Module
 * @copyright   Copyright (C) 2026 Hekla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * @var Joomla\Registry\Registry $params
 * @var string                   $uid
 */

// Register the module's CSS/JS. Important: Joomla does NOT automatically read 
// the joomla.asset.json file for a module (unlike with components/templates),
// so it is registered directly via PHP here. The assetExists() check
// prevents an “already registered” conflict if the module is included 
// multiple times on the same page; useStyle()/useScript() are idempotent
// anyway after that.
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

if (!$wa->assetExists('style', 'mod_popup.popup')) {
    $wa->registerStyle('mod_popup.popup', 'mod_popup/popup.css');
}

if (!$wa->assetExists('script', 'mod_popup.popup')) {
    $wa->registerScript('mod_popup.popup', 'mod_popup/popup.js', [], ['defer' => true]);
}

$wa->useStyle('mod_popup.popup')->useScript('mod_popup.popup');

$content         = (string) $params->get('content', '');
$linkUrl         = trim((string) $params->get('link_url', ''));
$linkTargetBlank = (int) $params->get('link_target_blank', 1);

$delayMs    = max(0, (int) $params->get('delay_ms', 5000));
$repeatMode = (string) $params->get('repeat_mode', 'once');
if (!in_array($repeatMode, ['once', 'repeated', 'cookie'], true)) {
    $repeatMode = 'once';
}
$cookieHours = max(1, (int) $params->get('cookie_hours', 24));

$vertical   = (string) $params->get('position_vertical', 'middle');
$horizontal = (string) $params->get('position_horizontal', 'center');

$alignMap   = ['top' => 'flex-start', 'middle' => 'center', 'bottom' => 'flex-end'];
$justifyMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
$offsetYMap = ['top' => -40, 'middle' => 0, 'bottom' => 40];
$offsetXMap = ['left' => -40, 'center' => 0, 'right' => 40];

$alignItems     = $alignMap[$vertical] ?? 'center';
$justifyContent = $justifyMap[$horizontal] ?? 'center';
$offsetY        = $offsetYMap[$vertical] ?? 0;
$offsetX        = $offsetXMap[$horizontal] ?? 0;

$overlayOpacity = max(0, min(90, (int) $params->get('overlay_opacity', 55))) / 100;

// Size
$widthMode  = (string) $params->get('width_mode', 'px');
$heightMode = (string) $params->get('height_mode', 'auto');

if (!in_array($widthMode, ['auto', 'px', 'percent', 'full'], true)) {
    $widthMode = 'px';
}
if (!in_array($heightMode, ['auto', 'px', 'percent', 'full'], true)) {
    $heightMode = 'auto';
}

switch ($widthMode) {
    case 'percent':
        $widthCss = max(1, min(100, (int) $params->get('width_percent', 60))) . '%';
        break;
    case 'full':
        $widthCss = '100%';
        break;
    case 'auto':
        $widthCss = 'auto';
        break;
    case 'px':
    default:
        $widthCss = max(100, (int) $params->get('width_px', 480)) . 'px';
        break;
}

switch ($heightMode) {
    case 'percent':
        $heightCss = max(1, min(100, (int) $params->get('height_percent', 60))) . '%';
        break;
    case 'full':
        $heightCss = '100%';
        break;
    case 'px':
        $heightCss = max(100, (int) $params->get('height_px', 400)) . 'px';
        break;
    case 'auto':
    default:
        $heightCss = 'auto';
        break;
}

// Overlay margin: 0 if the respective axis should be borderless (“full”).
$hPad = $widthMode === 'full' ? 0 : 24;
$vPad = $heightMode === 'full' ? 0 : 24;

// popup.css sets `max-width: 95vw` / `max-height: 95vh` as a safety net so
// that the box never extends beyond the edge of the screen. With “full,” this 
// would negate the desired 0-pixel margin, so it is overridden to 100% per 
// instance (inline styles take precedence over the class rule in popup.css).
$maxWidthCss  = $widthMode === 'full' ? '100%' : '95vw';
$maxHeightCss = $heightMode === 'full' ? '100%' : '95vh';

// Appearance
$bgColor      = (string) ($params->get('bg_color', '#ffffff') ?: '#ffffff');
$textColor    = trim((string) $params->get('text_color', '#222222'));
$linkColor    = trim((string) $params->get('link_color', ''));
$borderWidth  = max(0, (int) $params->get('border_width', 0));
$borderColor  = (string) ($params->get('border_color', '#cccccc') ?: '#cccccc');
$boxShadowKey = (string) $params->get('box_shadow', 'medium');

$shadowMap = [
    'none'   => 'none',
    'soft'   => '0 2px 10px rgba(0,0,0,.15)',
    'medium' => '0 10px 40px rgba(0,0,0,.3)',
    'strong' => '0 20px 60px rgba(0,0,0,.5)',
];
$shadowValue = $shadowMap[$boxShadowKey] ?? $shadowMap['medium'];

$borderValue = $borderWidth > 0
    ? $borderWidth . 'px solid ' . $borderColor
    : 'none';

$overlayStyle = sprintf(
    'align-items:%s;justify-content:%s;padding:%dpx %dpx;background-color:rgba(0,0,0,%s);',
    $alignItems,
    $justifyContent,
    $vPad,
    $hPad,
    $overlayOpacity
);

$boxStyle = sprintf(
    'width:%s;max-width:%s;height:%s;max-height:%s;background-color:%s;color:%s;border:%s;box-shadow:%s;--hkpopup-offset-x:%dpx;--hkpopup-offset-y:%dpx;',
    $widthCss,
    $maxWidthCss,
    $heightCss,
    $maxHeightCss,
    htmlspecialchars($bgColor, ENT_QUOTES),
    htmlspecialchars($textColor !== '' ? $textColor : '#222222', ENT_QUOTES),
    $borderValue,
    $shadowValue,
    $offsetX,
    $offsetY
);

if ($linkColor !== '') {
    $boxStyle .= sprintf('--hkpopup-link-color:%s;', htmlspecialchars($linkColor, ENT_QUOTES));
}
?>
<div id="<?php echo $uid; ?>"
     class="hkpopup-overlay"
     style="<?php echo $overlayStyle; ?>"
     data-delay="<?php echo $delayMs; ?>"
     data-repeat="<?php echo htmlspecialchars($repeatMode, ENT_QUOTES); ?>"
     data-cookie-hours="<?php echo $cookieHours; ?>">

    <div class="hkpopup-box" style="<?php echo $boxStyle; ?>">

        <button type="button" class="hkpopup-close" aria-label="<?php echo htmlspecialchars(Text::_('MOD_SIMPLEPOPUP_CLOSE'), ENT_QUOTES); ?>">&times;</button>

        <?php if ($linkUrl !== '') : ?>
            <a class="hkpopup-link-overlay"
               href="<?php echo htmlspecialchars($linkUrl, ENT_QUOTES); ?>"
               <?php echo $linkTargetBlank ? 'target="_blank" rel="noopener"' : ''; ?>
               aria-label="<?php echo htmlspecialchars(Text::_('MOD_SIMPLEPOPUP_LINK_LABEL'), ENT_QUOTES); ?>"></a>
        <?php endif; ?>

        <div class="hkpopup-content">
            <?php echo $content; ?>
        </div>

    </div>
</div>

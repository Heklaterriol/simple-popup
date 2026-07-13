<?php

/**
 * @package     Hekla.Module.Popup
 * @subpackage  mod_popup
 *
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

// CSS/JS des Moduls registrieren. Wichtig: Joomla liest die joomla.asset.json
// eines Moduls (anders als bei Komponenten/Templates) NICHT automatisch ein,
// daher wird hier direkt per PHP registriert. Die assetExists()-Prüfung
// verhindert eine "already registered"-Kollision, falls das Modul mehrfach
// auf derselben Seite eingebunden ist; useStyle()/useScript() sind danach
// ohnehin idempotent.
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
if (!in_array($repeatMode, ['once', 'repeated'], true)) {
    $repeatMode = 'once';
}

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

// Größe
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

// Randabstand des Overlays: 0, wenn die jeweilige Achse randlos ("full") sein soll.
$hPad = $widthMode === 'full' ? 0 : 24;
$vPad = $heightMode === 'full' ? 0 : 24;

// popup.css setzt als Sicherheitsnetz max-width:95vw / max-height:95vh, damit die
// Box nie über den Bildschirmrand hinausragt. Bei "full" würde das genau den
// gewünschten Rand von 0 wieder zunichtemachen, daher hier pro Instanz auf 100%
// überschrieben (Inline-Style hat Vorrang vor der Klassenregel in popup.css).
$maxWidthCss  = $widthMode === 'full' ? '100%' : '95vw';
$maxHeightCss = $heightMode === 'full' ? '100%' : '95vh';

// Erscheinungsbild
$bgColor      = (string) ($params->get('bg_color', '#ffffff') ?: '#ffffff');
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
    'width:%s;max-width:%s;height:%s;max-height:%s;background-color:%s;border:%s;box-shadow:%s;--hkpopup-offset-x:%dpx;--hkpopup-offset-y:%dpx;',
    $widthCss,
    $maxWidthCss,
    $heightCss,
    $maxHeightCss,
    htmlspecialchars($bgColor, ENT_QUOTES),
    $borderValue,
    $shadowValue,
    $offsetX,
    $offsetY
);
?>
<div id="<?php echo $uid; ?>"
     class="hkpopup-overlay"
     style="<?php echo $overlayStyle; ?>"
     data-delay="<?php echo $delayMs; ?>"
     data-repeat="<?php echo htmlspecialchars($repeatMode, ENT_QUOTES); ?>">

    <div class="hkpopup-box" style="<?php echo $boxStyle; ?>">

        <button type="button" class="hkpopup-close" aria-label="<?php echo htmlspecialchars(Text::_('MOD_POPUP_CLOSE'), ENT_QUOTES); ?>">&times;</button>

        <?php if ($linkUrl !== '') : ?>
            <a class="hkpopup-link-overlay"
               href="<?php echo htmlspecialchars($linkUrl, ENT_QUOTES); ?>"
               <?php echo $linkTargetBlank ? 'target="_blank" rel="noopener"' : ''; ?>
               aria-label="<?php echo htmlspecialchars(Text::_('MOD_POPUP_LINK_LABEL'), ENT_QUOTES); ?>"></a>
        <?php endif; ?>

        <div class="hkpopup-content">
            <?php echo $content; ?>
        </div>

    </div>
</div>

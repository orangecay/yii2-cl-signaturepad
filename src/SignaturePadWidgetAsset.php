<?php
/**
 * @link http://www.diggin-data.de/
 * @copyright Copyright (c) 2019 Diggin' Data
 * @license http://www.diggin-data.de/license/
 */

namespace diggindata\signaturepad;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Joachim Werner <joachim.werner@diggin-data.de> 
 */
class SignaturePadWidgetAsset extends AssetBundle
{
    public $sourcePath = '@vendor/diggindata/yii2-signaturepad/src/assets';
    public $css = [
        // 'css/ie9.css',
        'css/signature-pad.css'
    ];
    public $js = [
        'js/signature_pad.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}

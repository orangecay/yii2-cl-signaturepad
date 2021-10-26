<?php
/**
 * @link http://www.diggin-data.de/
 * @copyright Copyright (c) 2019 Diggin' Data
 * @license http://www.diggin-data.de/license/
 */

namespace diggindata\signaturepad;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\json;
use yii\web\View;

class SignaturePadWidget extends Widget
{
    private $_id;

    /**
     * Contains the signature data;
     * @var string
     */
    public $data;

    /**
     * The model which contains the signature attribute
     * @var yii\db\ActiveRecord or yii\base\Model
     */
    public $model;

    /**
     * The attribute name
     * @var string
     */
    public $attribute;

    /**
     * HTML attributes for the signature-pad container
     * @var mixed
     */
    public $options;

    /**
     * Whether to showe the Accept/Save (PNG) buttons
     * @var bool
     */
    public $showSaveAsPng = true;

    /**
     * Whether to showe the Accept/Save (JPG) buttons
     * @var bool
     */
    public $showSaveAsJpg = true;

    public $inputType = 'hidden';

    public function init()
    {
        $this->_id='signaturepad-'.uniqid();
        parent::init();
        if ($this->data === null) {
            $this->data = '';
        }
    }

    public function run()
    {
        /* DEBUG
        \yii\helpers\VarDumper::dump(array(
            'render' => $this->render,
            'text' => $this->text,
            'size' => $this->size,
        ), 10, true);
         */
        $view = $this->getView();
        SignaturePadWidgetAsset::register($view);
        $optionsString = '';
        if(is_array($this->options) and count($this->options)>0) {
            $optionsPairs = [];
            foreach($this->options as $k=>$v) {
                $optionsPairs[] = $k . '="'.$v.'"';
            }
            $optionsString = ' '.join(' ', $optionsPairs);
        }
        $classname = explode("\\", get_class($this->model));
        $classname = $classname[count($classname)-1];
        $txtDataId = strtolower($classname) . '-' . strtolower($this->attribute);
        $txtDataName = $classname . '[' . $this->attribute . ']';
        echo '<div class="form-group field-'.$txtDataId.'">'."\n";
        echo '    <label for="'.$txtDataId.'">Signature</label>'."\n";
        echo '    <input type="'.$this->inputType.'" id="'.$txtDataId.'" name="'.$txtDataName.'" class="form-control" />'."\n";
        echo '    <div id="'.$this->_id.'" class="signature-pad"'.$optionsString.'>';
        echo '        <div class="signature-pad--body">'."\n";
        echo '            <canvas></canvas>'."\n";
        echo '        </div>'."\n";

        echo '        <div>'."\n";
        echo '            '.Html::button('Clear', ['class'=>'button clear', 'data-action'=>'clear'])."\n";
        if($this->showSaveAsJpg) 
            echo '            '.Html::button('Accept (JPG)', ['class'=>'button accept', 'data-action'=>'accept-jpg'])."\n";
        if($this->showSaveAsPng) 
            echo '            '.Html::button('Accept (PNG)', ['class'=>'button accept', 'data-action'=>'accept-png'])."\n";
        if($this->showSaveAsJpg) 
                echo '            '.Html::button('Save as JPG', ['class'=>'button save', 'data-action'=>'save-jpg'])."\n";
        if($this->showSaveAsPng) 
            echo '            '.Html::button('Save as PNG', ['class'=>'button save', 'data-action'=>'save-png'])."\n";
        echo '        </div>'."\n";
        echo '    </div>';
        echo '<div>'."\n";

        $view->registerJs("
// Handle SignaturePad for div ".$this->_id."
var wrapper = document.getElementById('".$this->_id."');
var txtData = document.getElementById('".$txtDataId."');
var clearButton = wrapper.querySelector('[data-action=clear]');"
.($this->showSaveAsPng ? "
var acceptPNGButton = wrapper.querySelector('[data-action=accept-png]');
var savePNGButton = wrapper.querySelector('[data-action=save-png]');"
: "")
.($this->showSaveAsJpg ? "
var acceptJPGButton = wrapper.querySelector('[data-action=accept-jpg]');
var saveJPGButton = wrapper.querySelector('[data-action=save-jpg]');"
: "")."
var canvas = wrapper.querySelector('canvas');
var signaturePad = new SignaturePad(canvas, {
  // It's Necessary to use an opaque color when saving image as JPEG;
  // this option can be omitted if only saving as PNG or SVG
  backgroundColor: 'rgb(255, 255, 255)'
});

// Adjust canvas coordinate space taking into account pixel ratio,
// to make it look crisp on mobile devices.
// This also causes canvas to be cleared.
function resizeCanvas() {
  // When zoomed out to less than 100%, for some very strange reason,
  // some browsers report devicePixelRatio as less than 1
  // and only part of the canvas is cleared then.
  var ratio =  Math.max(window.devicePixelRatio || 1, 1);

  // This part causes the canvas to be cleared
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  canvas.getContext('2d').scale(ratio, ratio);

  // This library does not listen for canvas changes, so after the canvas is automatically
  // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
  // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
  // that the state of this library is consistent with visual state of the canvas, you
  // have to clear it manually.
  signaturePad.clear();
}

// On mobile devices it might make more sense to listen to orientation change,
// rather than window resize events.
window.onresize = resizeCanvas;
resizeCanvas();

function download(dataURL, filename) {
  var blob = dataURLToBlob(dataURL);
  var url = window.URL.createObjectURL(blob);

  var a = document.createElement('a');
  a.style = 'display: none';
  a.href = url;
  a.download = filename;

  document.body.appendChild(a);
  a.click();

  window.URL.revokeObjectURL(url);
}

// One could simply use Canvas#toBlob method instead, but it's just to show
// that it can be done using result of SignaturePad#toDataURL.
function dataURLToBlob(dataURL) {
  // Code taken from https://github.com/ebidel/filer.js
  var parts = dataURL.split(';base64,');
  var contentType = parts[0].split(':')[1];
  var raw = window.atob(parts[1]);
  var rawLength = raw.length;
  var uInt8Array = new Uint8Array(rawLength);

  for (var i = 0; i < rawLength; ++i) {
    uInt8Array[i] = raw.charCodeAt(i);
  }

  return new Blob([uInt8Array], { type: contentType });
}

clearButton.addEventListener('click', function (event) {
  signaturePad.clear();
});
"
.($this->showSaveAsPng ? "acceptPNGButton.addEventListener('click', function (event) {
  if (signaturePad.isEmpty()) {
    alert('Please provide a signature first.');
  } else {
    var dataURL = signaturePad.toDataURL('image/png');
    txtData.value = dataURL;
  }
});
savePNGButton.addEventListener('click', function (event) {
  if (signaturePad.isEmpty()) {
    alert('Please provide a signature first.');
  } else {
    var dataURL = signaturePad.toDataURL('image/png');
    download(dataURL, 'signature.png');
  }
});" : "")."
".($this->showSaveAsJpg ? "acceptJPGButton.addEventListener('click', function (event) {
  if (signaturePad.isEmpty()) {
    alert('Please provide a signature first.');
  } else {
    var dataURL = signaturePad.toDataURL('image/jpeg');
    txtData.value = dataURL;
  }
});
saveJPGButton.addEventListener('click', function (event) {
  if (signaturePad.isEmpty()) {
    alert('Please provide a signature first.');
  } else {
    var dataURL = signaturePad.toDataURL('image/jpeg');
    download(dataURL, 'signature.jpg');
  }
});" : "")
,
            View::POS_READY,
            'signature-pad-handler-'.$this->_id
        );
    }
}

SignaturePad Extension for Yii 2
================================

This extension provides a **Signature Pad** widget for [Yii framework 2.0](http://www.yiiframework.com).

It wraps the [INTELOGIE/signature_pad](https://github.com/INTELOGIE/signature_pad) library.

[Latest Stable Version](https://packagist.org/packages/diggindata/yii2-signaturepad)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist orangecay/yii2-cl-signaturepad
```

or add

```json
"orangecay/yii2-cl-signaturepad": "@dev"
```

to the require section of your composer.json, then run `composer update`.

Usage
-----

In your form model, declare an attribute:

```
    public $signature;

    public function rules()
    {
        return [
            ...
            ['signature', 'safe'],
        ];
    }
```
In your form view file, include the widget:

```
    <?= SignaturePadWidget::widget([
        'model' => $model,
        'attribute' => 'signature',
        'options' => ['style' => 'min-width:300px;min-height:200px;'],
        'showSaveAsJpg' => false,
    ]) ?>
```
### Attributes

**model** - The form instance, either a *yii\db\ActiveRecord* or a *yii\base\Model* instance

**attribute** - the model's attribute name

**showSaveAsJpg** - Whether to show the *Accept (JPG)* and *Save (JPG)* buttons

**showSaveAsPng** - Whether to show the *Accept (PNG)* and *Save (PNG)* buttons


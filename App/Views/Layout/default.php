<!DOCTYPE html>
<html>
<head>
    <?=$this->Html->meta(array('charset'=>'utf-8'))?>
    <?=$this->Html->title($title)?>
    <?=$this->Html->meta(array('name'=>'viewport', 'content'=>'width=device-width, initial-scale=1.0'))?>
    <?=$this->Html->meta(array('name'=>'author', 'content'=>'Benjamin Touchard'))?>
    <?=$this->Html->meta(array('name'=>'description', 'content'=>'Votre Freebox Révolution/HD et la Messagerie Vocale Visuelle FreeMobile sur Android'))?>
    <?=$this->Html->script('http://code.jquery.com/jquery-2.0.3.min.js')?>
    <?=$this->Html->script('user.js')?>
    <?=$this->Html->css('bootstrap/bootstrap.css')?>
    <?=$this->Html->css('bootstrap/bootstrap-responsive.css')?>
    <?=$this->Html->css('style.css')?>
</head>

<body>
    <? if (!empty($header)) $this->Html->header($header); ?>
    <div><?=$content?></div>
    <? if (!empty($footer)) $this->Html->footer($footer); ?>
</body>
</html>
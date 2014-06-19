<ul><?php foreach ($news as $new) { ?>
    <li><a href="<?=$this->Html->link('News', array($new->id, $new->title))?>"><?=$new->title?></a></li>
<?php } ?></ul>
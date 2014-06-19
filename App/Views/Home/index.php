<h1>Hello !</h1>
<? if (!empty($news)) { ?>
    <h2>Last News: <?=$news->title?></h2>
    <h3><?=$news->text?></h3>
<? } else { ?>
    <h2>Aucune actualitée a ce jour !</h2>
<? } ?>
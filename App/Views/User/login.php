<?  if (\App\Controller\UserController::logged()) { ?>
    <input type="button" onclick="document.location='<?=$this->Html->link('User', 'logout')?>'" value="Déconnexion">
<? } else { ?>
    <form name="user" method="post" action="<?=$this->Html->link('User', 'login')?>">
        <?=$this->Html->input(array('name'=>'login','value'=>''))?>
        <?=$this->Html->input(array('type'=>'password','name'=>'password','value'=>''))?>
        <?=$this->Html->input(array('type'=>'submit','value'=>'Connexion'))?>
    </form>
<? } ?>
<pre><?php echo $user;?></pre>
<?php
if (\App\Controller\UserController::logged()) {
    $click = 'document.location=\'' . $this->Html->link('User', 'logout') . '\'';
    echo $this->Html->input(array('type'=>'button', 'onclick'=>$click, 'value'=>'Déconnexion'));
}
?>
<script>
    $(function () {
        var url = "<?php echo $this->Html->link('User'); ?>";
        User.init(url);
    });
</script>
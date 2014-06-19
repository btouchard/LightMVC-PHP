<h3><?=$news->title?> <small>du <?=date('d M Y', strtotime($news->created))?></small></h3>
<p><?=$news->text?></p>
<?php
if (\App\Controller\UserController::logged()) {
    echo $this->Html->input(array('type'=>'button', 'onclick'=>'edit()', 'value'=>'Editer'));
}
?>
<script>
    function edit() {
        var url = "<?=$this->Html->link('News', array($news->id, 'edit'))?>";
        //alert("url: " + url);
        var xhr = getXMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
                alert(xhr.responseText);
            }
        };
        xhr.open("GET", url, true);
        xhr.send(null);
    }
    function getXMLHttpRequest() {
        var xhr = null;
        if (window.XMLHttpRequest || window.ActiveXObject) {
            if (window.ActiveXObject) {
                try {
                    xhr = new ActiveXObject("Msxml2.XMLHTTP");
                } catch(e) {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
            } else {
                xhr = new XMLHttpRequest();
            }
        } else {
            alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
            return null;
        }
        return xhr;
    }
</script>
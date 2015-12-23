<?php
include_once 'inc/header.php';
require_once 'inc/connexion.php';
$q = '';
if(isset($_GET['q']) && !empty($_GET['q'])){
    $q = trim(strip_tags($_GET['q']));
}
$req = $bdd->prepare('SELECT * FROM users WHERE nickname LIKE :pseudo');
$req->bindValue(':pseudo', '%'.$q.'%', PDO::PARAM_STR);
$req->execute();
$users = $req->fetchAll(PDO::FETCH_ASSOC);

?>
<main class="container">
    <form style="float:none;margin:0 auto;display:block">
        <input type="text" name="q" autofocus>
        <input type="submit" value="Rechercher">
    <?php if(!empty($q)): ?>
    <p>Résultats pour votre recherche : <?=$q; ?></p>
    <?php endif; ?>
    </form>
    <?php foreach($users as $user): ?>
    <article class="user">
        <h2><?=$user['nickname']; ?></h2>
        <p>Enregistré le <?=date('d/m/Y à H:i:s',strtotime($user['date_registered'])); ?></p>
    </article>
    <?php endforeach; ?>   
</main>
<?php include_once 'inc/footer.php'; ?>
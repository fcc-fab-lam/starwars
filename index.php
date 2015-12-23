<?php 

include_once 'inc/header.php';

require_once 'inc/connexion.php';
$debut = 0;
$nbMaxArt = 4;
$page = 1;
$afficheArticles = false;
$nbpages = 1;

if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0){
    $page = trim(strip_tags($_GET['page']));
}

$req1 = $bdd->prepare('SELECT COUNT(id) AS nb FROM posts');
$req1->execute();
$nbarticles = $req1->fetch(PDO::FETCH_ASSOC);
echo $nbarticles['nb'];

if($nbarticles['nb'] > $nbMaxArt){
    $nbpages = ceil($nbarticles['nb']/$nbMaxArt);
}

$debut = ($page-1)*$nbMaxArt;

$req2 = $bdd->prepare('SELECT * FROM posts LIMIT :start, :length');
$req2->bindValue(':start', $debut, PDO::PARAM_INT);
$req2->bindValue(':length', $nbMaxArt, PDO::PARAM_INT);
$req2->execute();
$articles = $req2->fetchAll(PDO::FETCH_ASSOC);
if(!empty($articles)){
    $afficheArticles = true;
}

?>
    <main class="container">
    <?php if($afficheArticles): ?>
        <?php foreach($articles as $key => $article): ?>
        <article>
            <h2><a href="article.php?id=<?=$article['id']; ?>"><?=$article['title']; ?></a></h2>
            <a href="article.php?id=<?=$article['id']; ?>"><img src="<?=$article['img']; ?>" alt="<?=$article['title']; ?>"></a>
            <p><a href="article.php?id=<?=$article['id']; ?>"><?=mb_substr($article['content'],0,200); ?>...</a></p>
            <a href="article.php?id=<?=$article['id']; ?>" class="lireSuite">Lire la suite &gt;</a>
        </article>
        <?php endforeach; ?>
                
            <?php if($afficheArticles): ?>
        <nav class="pagination">
            <ul>
                <li>Pages :</li>
                <?php for($i=1; $i<=$nbpages; $i++): ?>
                
                <?php if($i == $page): ?>
                <li class="active"><?=$i; ?></li>
                <?php else: ?>
                <li><a href="index.php?page=<?=$i; ?>"><?=$i; ?></a></li>
                <?php endif; ?>
                
                <?php endfor; ?>
            </ul>
        </nav>        
            <?php endif; ?>
        
    <?php else: ?>
        <p class="error">Aucun aticle à afficher pour cette page !</p>
    <?php endif; ?>
        
    </main>
<?php include_once 'inc/footer.php'; ?>

<?php include_once 'inc/header.php';

require_once 'inc/connexion.php';

if(!isset($_GET['id'])){
    header('location: index.php');
}
else{
    $postId =  trim(strip_tags($_GET['id']));
    $post = array();
    $err = array();
    $erreursForm = false;
    $formValid = false;

    if(!empty($_POST)){
        foreach ($_POST as $key => $value) {
            $post[$key] = trim(strip_tags($value));
        }
        if(empty($post['nickname'])){
            $err[] = 'Le pseudo ne peut être vide !';
        }
        if(empty($post['comment'])){
            $err[] = 'Vous devez saisir un commentaire !';
        }
        if(count($err) > 0){
            $erreursForm = true;
        }
        else {    
            $checkUser = $bdd->prepare('SELECT id FROM users WHERE nickname = :pseudo');
            $checkUser->bindValue(':pseudo', $post['nickname'], PDO::PARAM_STR);
            $checkUser->execute();

            $user = $checkUser->fetch(PDO::FETCH_ASSOC);

            if(isset($user['id']) && !empty($user['id'])){
                $userId = $user['id'];
            }
            else{
                $req1 = $bdd->prepare('INSERT INTO users (nickname, date_registered) VALUES (:pseudo, NOW())');
                $req1->bindValue(':pseudo', $post['nickname'], PDO::PARAM_STR);
                $req1->execute();
                $userId = $bdd->lastInsertId();   
            }

            if(!empty($userId)){

                $reqCommAdd = $bdd->prepare('INSERT INTO comments (comment, date, post_id, user_id) VALUES (:comment, NOW(), :postId, :userId)');
                $reqCommAdd->bindValue(':comment', $post['comment'], PDO::PARAM_STR);
                $reqCommAdd->bindValue(':postId', $postId, PDO::PARAM_INT);
                $reqCommAdd->bindValue(':userId', $userId, PDO::PARAM_INT);
                if($reqCommAdd->execute()){
                    $formValid = true;
                }
                else{
                    $err[] = 'Une erreur est survenue :(';
                    $erreursForm = true;
                }
            }
        }
    }
    $req = $bdd->prepare('SELECT * FROM posts WHERE id = :postId');
    $req->bindValue('postId', $_GET['id'], PDO::PARAM_INT);
    $req->execute();
    $article = $req->fetch(PDO::FETCH_ASSOC);
    
    $user = $bdd->prepare('SELECT nickname FROM users WHERE id = :userId');
    $user->bindValue('userId', $article['user_id'], PDO::PARAM_INT);
    $user->execute();
    $postAuthor = $user->fetchColumn();
    
    $reqComm = $bdd->prepare('SELECT * FROM comments WHERE post_id = :postId');
    $reqComm->bindValue('postId', $article['id'], PDO::PARAM_INT);
    $reqComm->execute();
    $comments = $reqComm->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <main class="container">
        <?php if(!empty($article)): ?>
            <article class="fullPost">
                <h2><?=$article['title']; ?></h2>
                <img src="<?=$article['img']; ?>" alt="<?=$article['title']; ?>">
                <p>
                    <?=$article['content']; ?>
                </p>
                <p class="postInfos">Posté par :
                    <?=$postAuthor; ?>, le
                        <?=date('d/m/Y à H:i:s', strtotime($article['date'])); ?>
                </p>
            </article>
            <form method="post">
                <h2>Ajouter un commentaire</h2>
                <label for="nickname">Pseudo</label>
                <input type="text" name="nickname" id="nickname" placeholder="Votre pseudo...">
                <label for="comment">Commentaire</label>
                <textarea name="comment" id="comment" placeholder="Votre commentaire..."></textarea>
                <p class="obligatoire">Les champs marqués d'une <span>*</span> sont obligatoires !</p>
                <input type="submit" value="Envoyer">
                <?php
    if($erreursForm){
        echo '<p class="error">'.implode('<br>', $err).'</p>';
    }
    if($formValid){
        echo '<p class="success">Le formulaire est valide, le commentaire est enregistré !</p>';
    }
    ?>
            </form>
            <?php if(!empty($comments)): ?>
            <div id="commentaires">
                <h2>Commentaires :</h2>
                <?php foreach($comments as $key => $comment):
    
                $userComm = $bdd->prepare('SELECT nickname FROM users WHERE id = :userId');
                $userComm->bindValue('userId', $comment['user_id'], PDO::PARAM_INT);
                $userComm->execute();
                $commentAuthor = $userComm->fetchColumn();
            
            ?>
                    <article class="commentaire">
                        <p>
                            <?=nl2br($comment['comment']); ?>
                        </p>
                        <p class="commentInfos">Posté par
                            <?=strtoupper($commentAuthor); ?>, le
                                <?=date('d/m/Y à H:i:s', strtotime($comment['date'])); ?>
                        </p>
                        <div class="flecheComm"></div>
                    </article>
                    <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <h2 class="error">Aucun article correspondant !</h2>
        <?php endif; ?>
    </main>
    <?php include_once 'inc/footer.php'; ?>

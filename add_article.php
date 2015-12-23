<?php 
include_once 'inc/header.php';
require_once 'inc/connexion.php';

$post = array();
$err = array();
$erreursForm = false;
$formValid = false;
$title = '';
$img = '';
$content = '';
$userNickname = '';
$maxFileSize = 1024 * 1000;
$mimeTypeAllowed = array('image/jpg','image/jpeg','image/png','image/gif');

if(!empty($_POST)){
    $finfo = new finfo();
    
	foreach ($_POST as $key => $value) {
		$post[$key] = trim(strip_tags($value));
	}

	if(empty($post['nickname'])){
		$err[] = 'Le pseudo ne peut être vide !';
	}
	if(empty($post['title'])){
		$err[] = 'Le titre ne peut être vide !';
	}
    if(empty($_FILES['img']['size'])){
        $err[] = 'L\'image ne peut être vide';        
    }
    elseif($_FILES['img']['size'] > $maxFileSize){
        $err[] = 'Le fichier image est trop gros !';
    }
    // if(!in_array($_FILES['img']['type'], $mimeTypeAllowed)){ // 1ere méthode
    $fileMimeType = $finfo->file($_FILES['img']['tmp_name'], FILEINFO_MIME_TYPE);
    if(!in_array($fileMimeType, $mimeTypeAllowed)){
        $err[] = 'Le fichier n\'est pas une image';
    }
	if(empty($post['content'])){
		$err[] = 'Vous devez saisir un article !';
	}
 
	if(count($err) > 0){
		$erreursForm = true;
        var_dump($err);
	}
	else {
		$formValid = true;
        
        $cheminImages = $_SERVER["DOCUMENT_ROOT"].'/starwars/';
        
        $search = array('à','â','ä','é','è','ê','ë','ï','î','ô','ö','ù','ü',' ');
        $replace = array('a','a','a','e','e','e','e','i','i','o','o','u','u','');
        
        $imgUploaded = 'images/'.time().str_replace($search, $replace, $_FILES['img']['name']);
        
        move_uploaded_file($_FILES['img']['tmp_name'], $cheminImages.$imgUploaded);
        
        $title = $post['title'];
        $content = $post['content'];
        $userNickname = $post['nickname'];
        
        $checkUser = $bdd->prepare('SELECT id FROM users WHERE nickname = :pseudo');
        $checkUser->bindValue(':pseudo', $userNickname, PDO::PARAM_STR);
        $checkUser->execute();
        
        $user = $checkUser->fetch(PDO::FETCH_ASSOC);
        
        if(isset($user['id']) && !empty($user['id'])){
            $userId = $user['id'];
        }
        else{
            $req1 = $bdd->prepare('INSERT INTO users (nickname, date_registered) VALUES (:pseudo, NOW())');
            $req1->bindValue(':pseudo', $userNickname, PDO::PARAM_STR);
            $req1->execute();
            $userId = $bdd->lastInsertId();   
        }
        
        $req2 = $bdd->prepare('INSERT INTO posts (title, img, content, date, user_id) VALUES (:titleArticle, :img, :contentArticle, NOW(), :userId)');
        $req2->bindValue(':titleArticle', $title, PDO::PARAM_STR);
        $req2->bindValue(':img', $imgUploaded, PDO::PARAM_STR);
        $req2->bindValue(':contentArticle', $content, PDO::PARAM_STR);
        $req2->bindValue(':userId', $userId, PDO::PARAM_INT);
        
        if($req2->execute()){
            $formValid = true;
        }
        else{
            $err[] = 'Une erreur est survenue !';
            $erreursForm = true;
        }
	}
}

?>
    <main class="container">        
            <form method="POST" class="fullPost" enctype="multipart/form-data">
                
                <h2>Ajouter un article</h2>
                <label for="nickname">Pseudo</label>
                <input type="text" name="nickname" id="nickname" placeholder="Votre pseudo..." value="<?=$userNickname; ?>">
                <label for="title">Titre</label>
                <input type="text" id="title" name="title" placeholder="Votre titre..." value="<?=$title; ?>">
                <label for="img">Image</label>
                <input type="file" id="img" name="img">
                <label for="content">Article</label>
                <textarea id="content" name="content" placeholder="Votre article..."><?=$content; ?></textarea>
                <input type="submit" value="Envoyer">
                <?php
            if($erreursForm){
                echo '<p class="error">'.implode('<br>', $err).'</p>';
            }
            if($formValid){
                echo '<p class="success">Le formulaire est valide, l\'article est enregistré !</p>';
            }
            ?>
            </form>
    </main>
<?php include_once 'inc/footer.php'; ?>

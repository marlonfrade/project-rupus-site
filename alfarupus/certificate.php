<?php

$db = new PDO('mysql:dbname=alfarupus;host=186.202.152.11', 'alfarupus', 'M0nkey_615243');
$db->exec("SET NAMES 'utf8'");
$db->exec('SET character_set_connection=utf8');
$db->exec('SET character_set_client=utf8');
$db->exec('SET character_set_results=utf8');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function fetchQtdQuestionAnsweredCorrectByModuleIdAndUserId(int $module_id, int $user_id)
{
    global $db;
    try {
        $sql = $db->prepare('SELECT count(module_id) as qtd FROM answer_has_user WHERE module_id = :module_id AND user_id = :user_id AND status = 1');
        $sql->bindValue(':user_id', $user_id);
        $sql->bindValue(':module_id', $module_id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC)['qtd'];
    } catch (Exception $e) {
        throw new Exception($e->getmessage());
    }
}
function fetchQtdQuestionByModuleId(int $module_id)
{
    global $db;
    try {
        $sql = $db->prepare('SELECT count(id) as qtd FROM question WHERE module_id = :module_id');
        $sql->bindValue(':module_id', $module_id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC)['qtd'];
    } catch (Exception $e) {
        throw new Exception($e->getmessage());
    }
}

function validadeModule($module_id, $user_id)
{
    $qtd_questions = fetchQtdQuestionByModuleId($module_id);
    $qtd_questions_answered_correct = fetchQtdQuestionAnsweredCorrectByModuleIdAndUserId($module_id, $user_id);
    if (!empty($qtd_questions) && !empty($qtd_questions_answered_correct)  && $qtd_questions == $qtd_questions_answered_correct) {
        return true;
    }
    return false;
}

function fetchModuleById($id)
{
    global $db;
    try {
        $sql = $db->prepare('SELECT * FROM module WHERE id = :id');
        $sql->bindValue(':id', $id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception($e->getmessage());
    }
}

function fetchUserById($id)
{
    global $db;
    try {
        $sql = $db->prepare('SELECT * FROM user WHERE id = :id');
        $sql->bindValue(':id', $id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception($e->getmessage());
    }
}

function makeCertificate($name, $module)
{
    $imgCouponBase = imagecreatefrompng("./certificates/certificate.png");
    $imageName = md5($name . $module) . '.png';
    $imageFile = './certificates/' . $imageName;
    $font = './assets/fonts/RedHatDisplay-Bold.ttf';

    // Image base width
    $imgCouponBaseWidth = imagesx($imgCouponBase);

    // Module's and user's bounding box
    $moduleBoundingBox = imagettfbbox(30, 0, $font, $module);
    $nameBoundingBox = imagettfbbox(16, 0, $font, $name);

    // Module's and user's position in the X axis
    $modulePositionX = ceil(($imgCouponBaseWidth - $moduleBoundingBox[2]) / 2);
    $namePositionX = ceil(($imgCouponBaseWidth - $nameBoundingBox[2]) / 2);

    $cor = imagecolorallocate($imgCouponBase, 44, 44, 44);
    imagettftext($imgCouponBase, 16, 0, $namePositionX, 295, $cor, $font, $name);

    $cor = imagecolorallocate($imgCouponBase, 255, 255, 255);
    imagettftext($imgCouponBase, 30, 0, $modulePositionX, 165, $cor, $font, $module);

    imagepng($imgCouponBase, $imageFile);
    imagedestroy($imgCouponBase);

    return $imageName;
}

if (!validadeModule($_GET['module'], $_GET['user_id'])) {
    die("<script>
    alert('Dados inválidos ou modulo não concluído!');
    window.location.href = 'platform.html';
    </script>");
}

$module = fetchModuleById($_GET['module']);
$user = fetchUserById($_GET['user_id']);

ob_start()
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado</title>
</head>

<body>
    <img src="./certificates/<?= makeCertificate($user['name'], $module['title']) ?>" />
</body>

</html>

<?php
$html = ob_get_contents();
ob_end_clean();

require_once __DIR__ . '/assets/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
    'orientation' => 'L'
]);
$mpdf->WriteHTML($html);
$mpdf->Output();
?>
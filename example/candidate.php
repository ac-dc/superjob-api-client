<?
/**
*	Очень простой пример работы с Superjob.ru API
*	Рассматривается вывод списков компаний, вакансий,
*	а так же вывод вакансий с контактами через OAuth
*
*	Для того, чтобы заработал пример, 
*	поправьте константы CLIENT_ID и CLIENT_SECRET
**/

header("Content-type: text/html; charset=utf-8");

include_once(dirname(__FILE__).'/../SuperjobAPI.php');
// ID app
define("CLIENT_ID", 233); 
// Secret key
define("CLIENT_SECRET", "secret_code_here");


try 
{
	$API = new SuperjobAPI(); //можно и так: SuperjobAPI::instance();
	$API->setSecretKey(CLIENT_SECRET);
	$clients = $API->clients(array('keyword' => 'Газпром', 'page' => 2, 'count' => 5));
	$vacancies = $API->vacancies(array('keyword' => 'php', 'town' => 4, 'page' => 1, 'count' => 5));
	
	$redirect_uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?access=1#oauth";
	
	if (!empty($_REQUEST['contacts']))
	{
		$API->redirectToAuthorizePage(CLIENT_ID,
			$redirect_uri, 'custom_data_value');
	}
	elseif (!empty($_REQUEST['access']))
	{
		$token_info = $API->fetchAccessToken($_REQUEST['code'], $redirect_uri, CLIENT_ID, CLIENT_SECRET);

		$access_token = $token_info['access_token'];
		$API->setAccessToken($access_token);
		
		// Под кем зашёл пользователь?
		$user = $API->current_user();

		$vacancies_with_contacts = $API->vacancies(
					array(
						'keyword' => 'php',
						'count' => 10, 
						't' => array(12, 13)
					)
				);
	}
}
catch (SuperjobAPIException $e)
{
	$error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>API SuperJob.ru Example</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/normalize.css">
	<link rel="stylesheet" href="css/code.css">
	<link rel="stylesheet" href="css/main.css">
</head>
<body>
<div class="g_layout">
	<div class="g_layout_wrapper">
<h1>API Superjob.ru. Примеры</h1>
<h2>Список компаний: clients</h2>
<div class="contacts">Ключевое слово: Газпром; вывод по 5 компаний; 3-я страница поиска.</div>
<table cellpadding=4 cellspacing=4>
<?
	foreach ($clients['objects'] as $v)
	{
		echo '<tr><td><p>
			<a href="'.$v['link'].'" target=_blank>'.$v['title'].'</a>
			</p></td><td>'.
			((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
			'</td></tr>';
	}
?>
</table>
<?
if (!empty($vacancies))
{
?>
<h2>Список вакансий: vacancies</h2>
<div class="contacts">Ключевое слово: php; город: Москва; вывод по 5 вакансий; 2-я страница поиска.</div>
<table cellpadding=4 cellspacing=4>
<?

	foreach ($vacancies['objects'] as $v)
	{
		echo '<tr><td><p>
			<a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a>
			</p></td><td>'.
			((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
			'</td></tr>';
	}
?>
</table>
<?
}
?>
<h2 id="oauth">Список вакансий с контактами: vacancies + OAuth</h2>
<div class="contacts">Ключевое слово: php; город: Н.Новгород, Новосибирск; вывод по 10 вакансий.
<br><b>Сессия теряется после перезагрузки страницы</b></div>
<p><a href="?contacts=1">Посмотреть</a></p>

<?
	
if (!empty($error))
{
	echo '<br><p><span style="color: #f00; font-weight: bold;">Ошибка: '.$error.'</span></p>';

}	
	
if (!empty($user))
{
	echo '<table cellpadding=4 cellspacing=4>';
	
	// Информация о текущем пользователе
	echo '<tr><td><p>Вы вошли как <b>'.$user['name'].'</b></td>';
	echo '<td>'.((!empty($user['photo']) ? '<img src="'.$user['photo'].'" border=0><br>' : '')).'</td></tr>';
	
	
	foreach ($vacancies_with_contacts['objects'] as $v)
	{
		echo '<tr><td>
			<p><a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a></p>
			<div class="contacts">'.
				($v['contact'].' &#9679; '.$v['phone'].' &#9679; '.$v['url']).
			'</div>
			</td>
			<td>'.
			((!empty($v['client_logo'])) 
				? '<img src="'.$v['client_logo'].'" border=0><br>' 
				: '').'
			</td></tr>';
	}
	echo '</table>';
}

?>
<div style="height: 200px; margin-bottom: 200px;"></div>
</div>
</div>
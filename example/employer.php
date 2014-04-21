<?
/**
*	Пример работы с Superjob.ru API для работодателя
	с использованием параллельных запросов
*
*	Для того, чтобы работал пример с OAuth, 
*	поправьте константы CLIENT_ID и CLIENT_SECRET
**/

header("Content-type: text/html; charset=utf-8");

include_once(dirname(__FILE__).'/../SuperjobAPI.php');
// ID app
define("CLIENT_ID", 1); 
// Secret key
define("CLIENT_SECRET", "secret_code_here");

try 
{
	$API = new SuperjobAPI();
	$redirect_uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?access=1#oauth";
	
	// Если хотим посмотреть резюме с контактами - нужно авторизоваться
	if (!empty($_REQUEST['contacts']))
	{
		$API->redirectToAuthorizePage(CLIENT_ID,
				$redirect_uri);
	}
	// Получили code - нужно запросить access_token
	elseif (!empty($_REQUEST['code']))
	{
		$token_info = $API->fetchAccessToken($_REQUEST['code'], $redirect_uri, CLIENT_ID, CLIENT_SECRET);

		$access_token = $token_info['access_token'];
		header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?access_token={$access_token}#access");
		exit;
	}
	// Есть access_token - можно смотреть любую информацию, требующую авторизации
	elseif(!empty($_REQUEST['access_token']))
	{
		$access_token = $_REQUEST['access_token'];
		// Под кем зашёл пользователь?
		$user = $API->current_user($access_token);
		
		// Выполняем запросы в параллельном режиме
		$API->setParallelMode();
		
		$resumes = $API->resumes(CLIENT_SECRET, array('keyword' => 'менеджер', 'gender' => 3, 'page' => mt_rand(0, 10), 'count' => 5));
		
		// Выбираем вакансии авторизованного пользователя
		$vacancies = $API->vacancies(CLIENT_SECRET, array('id_user' => $user['id'], 'published' => 1, 'count' => 3), $access_token);

		$resumes_with_contacts = $API->resumes(
					CLIENT_SECRET,
					array(
						'keyword' => 'хирург',
						'town' => 14,
						'count' => 5, 
					), 
					$access_token
				);

		// Выполняем предыдущие 3 запроса параллельно
		list($resumes, $vacancies, $resumes_with_contacts) = $API->executeParallel();
		
		if ($vacancies)
		{
			$API->setParallelMode();
			
			foreach ($vacancies['objects'] as $k => $v)
			{
				$received[$k] = $API->received_resumes_on_vacancy($v['id'], CLIENT_SECRET, $access_token, array('count' => 5));	
			}
			
			$received = $API->executeParallel();

			foreach ($vacancies['objects'] as $k => $v)
			{
				$vacancies['objects'][$k]['received'] = $received[$k];
			}
		}
	}
	else
	{
		$resumes = $API->resumes(CLIENT_SECRET, array('keyword' => 'менеджер', 'gender' => 3, 'page' => mt_rand(0, 10), 'count' => 5));
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
	<script type="text/javascript" src="http://yandex.st/jquery/2.1.0/jquery.min.js"></script>
</head>
<body>
<div class="g_layout">
	<div class="g_layout_wrapper">
<h1>API Superjob.ru для работодателя. Примеры</h1>


<h2 id="oauth">Список полученных резюме на вакансии: resumes/received/:id + OAuth</h2>


<p><a href="?contacts=1"><?php echo empty($_REQUEST['access_token']) ? 'Посмотреть' : 'Обновить'?></a></p>

<?
	
if (!empty($error))
{
	echo '<br><p><span class="error">Ошибка: '.$error.'</span></p>';

}	
	
if (!empty($user) && !empty($user['hr']))
{
	echo '<table cellpadding=4 cellspacing=4>';
	
	// Информация о текущем пользователе
	echo '<tr><td><p>Вы вошли как <b>'.$user['name'].'</b></td>';
	echo '<td>'.((!empty($user['photo']) ? '<img src="'.$user['photo'].'" border=0><br>' : '')).'</td></tr>';
	
	if (!empty($vacancies))
	{
?>
<table cellpadding=4 cellspacing=4>
<?
		foreach ($vacancies['objects'] as $v)
		{
			$rstring = '';
			if (!empty($v['received']))
			{
				foreach ($v['received']['objects'] as $rlist)
				{
					$resume = $rlist['resume'];
					$rstring.= '<div class="received"><p class="cutted"><a href="'.$resume['link'].'" target=_blank>'.$resume['profession'].'</a></p>
								<div class="contacts">'.
									($resume['firstname'].' &#9679; '.$resume['phone1'].' &#9679; '.$resume['email']).
								'</div></div>';
				}
			}
			echo '<tr><td colspan=2><p>
				<a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a>
				'.($v['received']['total'] 
					? '&nbsp;&nbsp; 
						<a href="#" onclick="$(\'#v'.$v['id'].'\').toggle(); return false;"><small>Полученные резюме ('.$v['received']['total'].')</small></a>' 
					: '').
				'</p><br>
				<div class="hide" id="v'.$v['id'].'">'.$rstring.'</div></td></tr>';
		}
?>
</table>
<?
	}
	if ($resumes_with_contacts)
	{
?>
<table cellpadding=4 cellspacing=4>
<h2 id="oauth">Список резюме с контактами: /resumes + OAuth</h2>
<div class="contacts">Город: Санкт-Петербург; ключевое слово: хирург; вывод по 5 резюме.</div>
<?
	
		foreach ($resumes_with_contacts['objects'] as $v)
		{
			echo '<tr><td>
				<p class="cutted"><a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a></p>
				<div class="contacts">'.
					($v['firstname'].' &#9679; '.$v['phone1'].' &#9679; '.$v['email']).
				'</div>
				</td>
				<td>'.
				((!empty($v['photo'])) 
					? '<img src="'.$v['photo'].'" border=0><br>' 
					: '').'
				</td></tr>';
		}
		echo '</table>';
	}
}

	if (!empty($resumes))
	{
?>
<h2>Поиск резюме без контактов</h2>
<div class="contacts">Ключевое слово: менеджер; пол: женский; вывод по 5 резюме; 3-я страница поиска.</div>
<table cellpadding=4 cellspacing=4>
<?	
		foreach ($resumes['objects'] as $v)
		{
			echo '<tr><td><p class="cutted">
				<a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a>
				</p></td><td>'.
				((!empty($v['photo'])) ? '<img src="'.$v['photo'].'" border=0><br>' : '').
				'</td></tr>';
		}
		echo '</table>';		
	}

?>

<div class="bottom"></div>
</div>
</div>
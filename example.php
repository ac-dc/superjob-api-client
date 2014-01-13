<?
/**
*	Очень простой пример работы с Superjob.ru API
*	Рассматривается вывод списков компаний, вакансий,
*	а так же вывод вакансий с контактами через OAuth
*
*	Для того, чтобы работал пример с OAuth, 
*	поправьте настройки в файле config.php
**/
session_start();
header("Content-type: text/html; charset=utf-8");

include_once('SuperjobAPIClient.php');

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
<?php

try 
{
	$APIClient = new SuperjobAPIClient(); //можно и так: SuperjobAPIClient::instance();
	
?>
<div class="g_layout">
	<div class="g_layout_wrapper">
<h1>API Superjob.ru. Примеры</h1>
<h2>Список компаний: clients</h2>
<div class="contacts">Ключевое слово: Газпром; вывод по 5 компаний; 3-я страница поиска.</div>
<?

	$clients = $APIClient->clients(array('keyword' => 'Газпром', 'page' => 2, 'count' => 5));

	if (!$APIClient->hasError())
	{
		echo '<table cellpadding=4 cellspacing=4>';
		foreach ($clients['objects'] as $v)
		{
			echo '<tr><td><p>
				<a href="'.$v['link'].'" target=_blank>'.$v['title'].'</a>
				</p></td><td>'.
				((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
				'</td></tr>';
		}
		echo '</table>';
	}
?>
<h2>Список вакансий: vacancies</h2>
<div class="contacts">Ключевое слово: php; город: Москва; вывод по 5 вакансий; 2-я страница поиска.</div>
<?


	$vacancies = $APIClient->vacancies(array('keyword' => 'php', 'town' => 4, 'page' => 1, 'count' => 5));

	if (!$APIClient->hasError())
	{
		echo '<table cellpadding=4 cellspacing=4>';
		foreach ($vacancies['objects'] as $v)
		{
			echo '<tr><td><p>
				<a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a>
				</p></td><td>'.
				((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
				'</td></tr>';
		}
		echo '</table>';
	}
?>
<h2 id="oauth">Список вакансий с контактами: vacancies + OAuth</h2>
<div class="contacts">Ключевое слово: php; город: Н.Новгород, Новосибирск; вывод по 10 вакансий.
<br><b>Сессия теряется после перезагрузки страницы</b></div>
<p><a href="?contacts=1">Посмотреть</a></p>
<?
	if (!empty($_REQUEST['contacts']))
	{
		$Request = $APIClient->fetchRequestToken();
		
		// Запоминаем request токен, чтобы потом получить access токен
		$_SESSION['oauth_token'] = $Request->key;
		$_SESSION['oauth_token_secret'] = $Request->secret;
	
		$APIClient->redirectToAuthorizePage($Request, 
			"http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?access=1#oauth");
	}
	elseif (!empty($_REQUEST['access']))
	{
		$Access = $APIClient->fetchAccessToken(new OAuthToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']));
		
		// Под кем зашёл пользователь?
		$user = $APIClient->current_user($Access);

		$vacancies = $APIClient->vacancies(
					array(
						'keyword' => 'php',
						'count' => 10, 
						't' => array(12, 13)
					), 
					$Access
				);
				
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		
		if (!$APIClient->hasError())
		{
			echo '<table cellpadding=4 cellspacing=4>';
			
			// Информация о текущем пользователе
			echo '<tr><td><p>Вы вошли как <b>'.$user['name'].'</b></td>';
			echo '<td>'.((!empty($user['photo']) ? '<img src="'.$user['photo'].'" border=0><br>' : '')).'</td></tr>';
			
			
			foreach ($vacancies['objects'] as $v)
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
		else
		{
			// Обычные ошибки приходят в массиве, но ошибки OAuth в обычном тексте
			$error = (is_array($vacancies)) ? $vacancies['error']['message'] : $vacancies;
			echo '<p><b>'.$error.'</b></p>';
		}
	}
}
catch (Exception $e)
{
	echo $e->getMessage();
}

?>
<br><br><br><br><br><br><br><br><br><br><br><br>
</div>
</div>
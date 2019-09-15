<header class="main-header">
	<a href="/" class="inner-bound">
		<h1 class="site-title">Kinderflohmarkt Erbach</h1>
		<h2 class="site-subtitle">in der Erlenbachhalle</h2>
	</a>
</header>
{IF({ISSET:cmt_visitorloggedin:SESSION})}
<div class="userbar">
	{SESSIONVAR:seller_nr} | {SESSIONVAR:seller_firstname} {SESSIONVAR:seller_lastname} | &lt;{SESSIONVAR:seller_email}&gt; | <a class="" href="{PAGEURL:17}?action=logout">Abmelden</a>
</div>
{ENDIF}

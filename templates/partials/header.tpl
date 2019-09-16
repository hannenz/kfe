<header class="main-header">
	<a href="/" class="inner-bound">
		<h1 class="site-title">Kinderflohmarkt Erbach</h1>
		<h2 class="site-subtitle">in der Erlenbachhalle</h2>
	</a>
</header>
{IF({ISSET:cmt_visitorloggedin:SESSION})}
<div class="userbar">
	Verkäufernummer <b>{SESSIONVAR:seller_nr}</b> &middot; {SESSIONVAR:seller_firstname} {SESSIONVAR:seller_lastname} &middot; <a class="" href="{PAGEURL:6}">Etiketten drucken</a> &middot; <a class="" href="{PAGEURL:17}?action=logout">Abmelden</a>
</div>
{ENDIF}

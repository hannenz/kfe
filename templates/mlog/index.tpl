<section class="news">
	<ul>
		{LOOP VAR(posts)}
		<li>
			<article class="post">
				<header>
					<h1>{VAR:post_title}</h1>
				</header>
			</article>
		</li>
		{ENDLOOP VAR}
	</ul>

</section>

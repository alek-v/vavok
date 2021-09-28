<div class="blog-page">
	<div class="row">
		<div class="col-sm">
			<div class="rounded-circle blog-date">
				{@date-published-day}}<br />{@date-published-month}}
			</div>
		</div>
	</div>
	<div class="blog-content">
		{@content}}
	</div>

	<div class="col-sm blog-author"> <!-- Author and date -->
		{@website_language[author]}}: {@author_link}}<br />
		{@website_language[published]}}: {@published_date}}<br />
		{@website_language[updated]}}: {@date_updated}}
	</div>

	<div id="tags_area">
		{@tags}} <!-- tags -->
	</div>

	<div id="comment_area">
		{@comments}} <!-- Comments -->

		{@add_comment}} <!-- Add comment -->
	</div>

	<div>
		<p>
			<a href="/blog/" class="btn btn-primary">{@website_language[backtoblog]}}</a>
		</p>
	</div>
</div>
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
		{@localization[author]}}: {@author_link}}<br />
		{@localization[published]}}: {@published_date}}<br />
		{@localization[updated]}}: {@date_updated}}
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
			<a href="/blog/" class="btn btn-primary">{@localization[backtoblog]}}</a>
		</p>
	</div>
</div>
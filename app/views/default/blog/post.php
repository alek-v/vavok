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
    <!-- author and date -->
    <div class="col-sm blog-author">
        {@localization[author]}}: {@author_link}}<br />
        {@localization[published]}}: {@published_date}}<br />
        {@localization[updated]}}: {@date_updated}}
    </div>
    <!-- tags -->
    <div id="tags-area">
        {@tags}}
    </div>
    <!-- comments -->
    <div id="comment-area">
        {@comments}}
        {@add_comment}}
    </div>
</div>
<kb:if condition="$pagination->hasPages()">
    <nav class="pagination">
        <kb:if condition="$pagination->hasPrevPage()">
            <a class="pagination-prev" href="{{ $pagination->prevPageUrl(); }}">{{ $pagination->prevPageUrl(); }}</a>
            <kb:else>
                <span class="pagination-prev">&larr;</span>
            </kb:else>
        </kb:if>
        <kb:if condition="$pagination->hasNextPage()">
            <a class="pagination-next" href="{{ $pagination->nextPageUrl(); }}">{{ $pagination->nextPageUrl(); }}</a>
            <kb:else>
                <span class="pagination-next">&rarr;</span>
            </kb:else>
        </kb:if>
    </nav>
</kb:if>
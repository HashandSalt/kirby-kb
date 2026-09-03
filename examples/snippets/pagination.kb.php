<kb:if condition="$pagination->hasPages()">
    <nav class="pagination">
        <kb:if condition="$pagination->hasPrevPage()">
            <a class="pagination-prev" href="<kb:php>echo $pagination->prevPageUrl();</kb:php>">&larr;</a>
            <kb:else>
                <span class="pagination-prev">&larr;</span>
            </kb:else>
        </kb:if>
        <kb:if condition="$pagination->hasNextPage()">
            <a class="pagination-next" href="<kb:php>echo $pagination->nextPageUrl();</kb:php>">&rarr;</a>
            <kb:else>
                <span class="pagination-next">&rarr;</span>
            </kb:else>
        </kb:if>
    </nav>
</kb:if>
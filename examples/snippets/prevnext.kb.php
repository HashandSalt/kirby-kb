<nav class="blog-prevnext">
  <h2 class="h2">Keep on reading</h2>

  <div class="autogrid" style="--gutter: 1.5rem">
    <kb:if condition="$prev = $page->prevListed()">
      <kb:snippet name="note" note="$prev" page="$prev" excerpt="true" />
    </kb:if>

    <kb:if condition="$next = $page->nextListed()">
      <kb:snippet name="note" note="$next" page="$next" excerpt="true" />
    </kb:if>
  </div>
</nav>
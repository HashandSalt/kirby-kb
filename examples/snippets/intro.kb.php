<header class="h1">
  <h1>{{ $page->headline()->or($page->title()) }}</h1>
  <kb:if condition="$page->subheadline()->isNotEmpty()">
    <p class="color-grey">{{ $page->subheadline() }}</p>
  </kb:if>
</header>
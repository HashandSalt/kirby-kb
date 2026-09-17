<kb:snippet name="header" />

<kb:if condition="$cover = $page->cover()">
  <kb:a url="$cover->url()" data-lightbox class="img" style="--w:2; --h:1">
    <kb:image src="$cover" mode="crop" width="1200" height="600" quality="80" alt="$cover->alt()->esc()" />
  </kb:a>
</kb:if>

<article class="note">
  <header class="note-header h1">
    <h1 class="note-title">
      <kb:title />
    </h1>

    <kb:if-field name="subheading">
      <p class="note-subheading"><small>{{ $page->subheading()->esc() }}</small></p>
    </kb:if-field>
  </header>

  <div class="note text">
    <kb:blocks field="text" />
  </div>

  <footer class="note-footer">
    <kb:tags wrapclass="note-tags" wraptag="ul" breaktag="li" />
    <kb:date field="date" format="d M, Y" class="note-date">Published on</kb:date>
  </footer>

  <kb:snippet name="prevnext" />
</article>

<kb:snippet name="footer" />
<article class="note-excerpt">
  <kb:a url="$note">
    <header>
      <figure class="img" style="--w: 16; --h:9">
        <kb:if condition="$cover = $note->cover()">
          <kb:image src="$cover" mode="crop" quality="80" width="320" height="180" alt="$cover->alt()->esc()" />
        </kb:if>
      </figure>
      <h2 class="note-excerpt-title">{{ $note->title()->esc() }}</h2>

      <kb:date field="date" format="d M, Y" class="note-excerpt-date" />
    </header>

    <kb:if condition="$excerpt">
      <div class="note-excerpt-text">
        <kb:excerpt field="text" chars="280" />
      </div>
      <kb:else />
      <div class="note-excerpt-text">
        <kb:blocks field="text" />
      </div>
    </kb:if>

  </kb:a>
</article>
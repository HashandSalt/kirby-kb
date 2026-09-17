<kb:snippet name="header" />
<kb:snippet name="intro" />

<kb:if condition="$photographyPage">

  <ul class="home-grid">
    <kb:foreach items="$photographyPage->children()->listed()" as="album">
      <li>
        <kb:a url="$album">
          <figure>
            <kb:image width="1024" mode="resize" format="webp" height="1024" src="$album->cover()"
              alt="{{ $album->cover()->alt()->esc() }}" />
            <figcaption>
              <span>
                <span class="example-name">{{ $album->title()->esc() }}</span>
              </span>
            </figcaption>
          </figure>
        </kb:a>
      </li>
    </kb:foreach>
  </ul>

</kb:if>

<kb:snippet name="footer" />
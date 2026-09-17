<kb:snippet name="header" />
<article>
  <kb:snippet name="intro" />
  <div class="grid">

    <div class="column" style="--columns: 4">
      <div class="text">
        <kb:field name="text" kt="true" />
      </div>
    </div>

    <div class="column" style="--columns: 8">
      <ul class="album-gallery">

        <kb:foreach items="$gallery" as="image">
          <li>
            <kb:a url="{{ $image->url() }}" data-lightbox>
              <figure class="img" style="--w:{{ $image->width() }};--h:{{ $image->height() }}">
                <kb:img src="{{ $image->url() }}" mode="resize" width="400" height="500" format="webp" quality="80"
                  alt="{{ $image->alt()->esc() }}" />
              </figure>
            </kb:a>
          </li>
        </kb:foreach>

      </ul>
    </div>

</article>
<kb:snippet name="footer" />
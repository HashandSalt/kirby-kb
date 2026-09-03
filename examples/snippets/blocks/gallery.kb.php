<figure class="gallery">
  <ul>
    <kb:foreach items="$block->images()->toFiles()" as="image">
      <li>
        <kb:snippet
          name="image"
          alt="$image->alt()"
          contain="$block->crop()->isTrue()"
          lightbox="true"
          href="$image->url()"
          src="$image->url()"
          ratio="$block->ratio()->or('auto')"
        />
      </li>
    </kb:foreach>
  </ul>

  <kb:if condition="$block->caption()->isNotEmpty()">
    <figcaption>{{ $block->caption()->value() }}</figcaption>
  </kb:if>
</figure>

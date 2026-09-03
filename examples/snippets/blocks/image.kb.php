<kb:if condition="$block->location()->value() === 'web' || $block->image()->toFile()">
  <figure>
    <kb:snippet
      name="image"
      alt="$block->alt()->or($block->image()->toFile()?->alt())"
      contain="$block->crop()->isFalse()"
      lightbox="$block->link()->isEmpty()"
      href="$block->link()->isNotEmpty() ? $block->link()->toUrl() : ($block->location()->value() === 'web' ? $block->src() : $block->image()->toFile()?->url())"
      src="$block->location()->value() === 'web' ? $block->src() : $block->image()->toFile()?->url()"
      ratio="$block->ratio()->or('auto')"
    />

    <kb:if condition="$block->caption()->isNotEmpty()">
      <figcaption class="img-caption">{{ $block->caption() }}</figcaption>
    </kb:if>
  </figure>
</kb:if>

<kb:if condition="$block->url()->isNotEmpty()">
  <figure>
    <span class="video" style="--w:16;--h:9">
      <kb:video url="$block->url()" />
    </span>

    <kb:if condition="$block->caption()->isNotEmpty()">
      <figcaption class="video-caption">{{ $block->caption() }}</figcaption>
    </kb:if>
  </figure>
</kb:if>

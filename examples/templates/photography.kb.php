<kb:snippet name="header" />
<kb:snippet name="intro" />

<ul class="grid" style="--gutter: 1.5rem">

  <kb:foreach items="$page->children()->listed()" as="project">
    <li class="column" style="--columns: 3">
      <kb:a url="$project">
        <figure>
          <span class="img" style="--w:4;--h:5">
            <kb:if condition="$cover = $project->cover()">
              <kb:img src="$project->cover()" mode="resize" width="400" height="500" format="webp" alt="$cover->alt()->esc()" />
              <kb:else />
              <p>No cover image</p>
            </kb:if>
          </span>
          <figcaption class="img-caption">
            <kb:title page="$project" />
          </figcaption>
        </figure>
      </kb:a>
    </li>
  </kb:foreach>

</ul>

<kb:snippet name="footer" />
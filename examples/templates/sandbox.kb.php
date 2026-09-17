<kb:snippet name="header" />

<article>

  <!-- KB:TITLE -->
  <h1 class="h1">
    <kb:title />
  </h1>

  <!-- KB:FIELD -->
  <div class="kb-example">
    <kb:field name="text" />
  </div>

  <!-- KB:EMAIL -->
  <div class="kb-example ">
    <p>
      <kb:email address="email@hashandsalt.com" text="Email me" class="email-link" />
    </p>
    <p>
      <kb:email field="postauthor" text="Email the author" class="email-link" />
    </p>
  </div>

  <!-- KB:TEL -->
  <div class="kb-example ">
    <p>
      <kb:tel number="+1234567890" text="Call me" class="tel-link" />
    </p>
    <p>
      <kb:tel field="telnumber" text="Call me" class="tel-link" />
    </p>
  </div>

  <!-- KB:DATE -->
  <div class="kb-example">
    <p>
      <kb:date field="postdate" format="d M, Y">Published on</kb:date>
    </p>
    <p> Copyright &copy; 2021 -
      <kb:date format="Y" />
    </p>
  </div>

  <!-- KB:CSS -->
  <kb:css files="prism.css, lightbox.css, index.css, @auto" />

  <!-- KB:JS -->
  <kb:js files="prism.js, lightbox.js, index.js, @auto" />

  <!-- KB:EXCERPT -->
  <div class="kb-example">
    <kb:excerpt field="text" />
    <kb:excerpt field="text" chars="80" />
  </div>

  <!-- KB:PREV TITLE & NEXT TITLE -->
  <div class="kb-example">
    <kb:prev-title />
    <kb:next-title />
  </div>

  <!-- KB:PREV & NEXT -->
  <div class="kb-example">
    <kb:prev />
    <kb:next />
  </div>

  <!-- KB:PREV LINK & NEXT LINK -->
  <div class="kb-example">
    <kb:prev>Previous post - <kb:prev-title /> </kb:prev>
    <kb:next>Next post - <kb:next-title /> </kb:next>
  </div>

  <!-- KB:PERMALINK -->
  <div class="kb-example">
    <kb:permalink page="photography" />
    <kb:permalink page="notes" />
  </div>

  <!-- KB:LINK -->
  <kb:link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />

  <!-- KB:BLOCKS -->
  <kb:blocks field="blockexample" />

  <!-- KB:A -->
  <kb:a url="{{ $page->url() }}">{{ $page->title() }}</kb:a>
  <kb:a url="$page->url()">{{ $page->title() }}</kb:a>
  <kb:a url="$somevar">{{ $page->title() }}</kb:a>

  <!-- KB:IMAGE -->
  <!-- from a file name -->
  <kb:image src="team.jpg" mode="crop" quality="80" format="webp" object-fit="cover" ratio="16/9" width="1440"
    height="450" class="team-image" />

  <!-- short version -->
  <kb:img src="team.jpg" mode="crop" quality="80" format="webp" object-fit="cover" ratio="16/9" width="1440"
    height="450" class="team-image" />

  <!-- via cover() set in model -->
  <kb:if condition="$cover = $page->cover()">
    <kb:image src="$cover" mode="crop" quality="80" format="webp" object-fit="contain" ratio="16/9" width="1440"
      height="450" class="team-image" alt="$cover->alt()->esc()" />
  </kb:if>

  <!-- via template / controller var -->
  <kb:image src="$pageimg" mode="crop" quality="80" format="webp" object-fit="contain" ratio="16/9" width="1440"
    height="450" class="team-image" alt="$pageimg->alt()->esc()" />

  <!-- fallback image if no page image is set -->
  <kb:if condition="!$pageimg">
    <kb:image src="team.jpg" mode="crop" quality="80" format="webp" object-fit="contain" ratio="16/9" width="1440"
      height="450" class="fallback-image" alt="Fallback image" />
  </kb:if>

  <!-- KB:VIDEO -->
  <kb:video src="forest.mp4" poster="forest.jpg" controls="true" width="640" height="360" />

  <!-- KB:VIMEO -->
  <kb:vimeo url="https://vimeo.com/253905163" width="640" height="360" />

  <!-- KB:YOUTUBE -->
  <kb:youtube url="https://www.youtube.com/watch?v=UKMK31-jjhw" width="640" height="360" />

  <!-- KB:QR -->
  <kb:qr data="https://www.getkirby.com" />

  <!-- KB:GIST -->
  <kb:gist url="https://gist.github.com/lukaskleinschmidt/cf97ebff8901053df2b085db6d28c7e2" file="blueprint.yaml" />

  <!-- KB:SVG -->
  <kb:svg src="kirby.svg" />

  <!-- KB:SECTION -->
  <kb:section />

  <!-- KB:BREADCRUMB -->
  <kb:breadcrumb />

  <!-- KB:TAGS -->
  <kb:tags field="sometags" class="tag-list" wraptag="ul" breaktag="li" breakclass="tag" />

  <!-- KB:IF -->
  <kb:if condition="$page->hasFiles()">
    <p>The page has files.</p>
    <kb:else />
    <p">The page has no files.</p>
  </kb:if>

  <!-- KB:IF-FIELD -->
  <kb:if-field name="sometags">
    <p>The page has a tags field named sometags and it's not empty:</p>
    <kb:tags field="sometags" />
  </kb:if-field>

  <!-- KB:FOREACH -->
  <kb:foreach items="$site->children()->listed()" as="item">
    <p>{{ $item->title()->esc() }}</p>
  </kb:foreach>

  <ul>
    <kb:foreach items="$site->children()->listed()" as="item">
      <li>
        <kb:if condition="$item->isOpen()">
          <kb:a aria-current="page" url="{{ $item->url() }}">{{ $item->title()->esc() }}</kb:a>
          <kb:else />
          <kb:a url="{{ $item->url() }}">{{ $item->title()->esc() }}</kb:a>
        </kb:if>
      </li>
    </kb:foreach>
  </ul>

  <!-- KB:STRUCTURE -->
  <kb:structure field="social" as="platform" wraptag="ul" breaktag="li" class="contact-social"
    breakclass="contact-social-item">
    <kb:a url="{{ $platform->url()->esc() }}" target="_blank" rel="noopener noreferrer">
      {{ $platform->platform()->esc() }}
    </kb:a>
  </kb:structure>

  <!-- KB:PHP -->
  <kb:php>
    echo $page->title()->esc();
  </kb:php>

  <!-- KB:SNIPPET -->
  <kb:snippet name="header" />

  <!-- KB:PAGES -->
  <kb:pages section="photography" wraptag="ul" class="page-list-feature" breakclass="page-list-item" breaktag="li"
    mode="listed" limit="1" />
  <kb:pages section="photography" wraptag="ul" class="page-list" breakclass="page-list-item" breaktag="li" mode="listed"
    offset="1" />


</article>

<kb:snippet name="footer" />
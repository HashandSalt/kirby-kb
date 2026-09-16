# KB template language for Kirby

A new template language created to make it easier to build sites. The language uses HTML like tags to to make templates and snippets more readable and succint. It is largely inspired by the TXP template language used by Textpattern.

It makes it easier to create things like for each loops loops and if conditions by using special, Kirby specific html tags where the tags attributes are passed through to Kirby's page/file/user methods under the hood.

But what  can I do with it? Good question! Here is a small example:

```html
<kb:foreach items="$page->children()->listed()" as="project">
<li class="column" style="--columns: 3">
<kb:a url="$project">
<figure>
  <span class="img" style="--w:4;--h:5">
    <kb:if condition="$cover = $project->cover()">
      <kb:img src="$project->cover()" mode="resize" width="400" height="500" format="webp" alt="$cover->alt()->esc()" />
    </kb:if>
  </span>
  <figcaption class="img-caption">
    <kb:title page="$project" />
  </figcaption>
</figure>
</kb:a>
</li>
</kb:foreach>
```

This is equavalent to the PHP & HTML mashup:

```php
<?php foreach ($page->children()->listed() as $project): ?>
<li class="column" style="--columns: 3">
<a href="<?= $project->url() ?>">
  <figure>
    <span class="img" style="--w:4;--h:5">
      <?php if ($cover = $project->cover()): ?>
        <img src="<?= $cover->crop(400, 500)->url() ?>" alt="<?= $cover->alt()->esc() ?>">
      <?php endif ?>
    </span>
    <figcaption class="img-caption">
      <?= $project->title()->esc() ?>
    </figcaption>
  </figure>
</a>
</li>
<?php endforeach ?>
```

See how it becomes more readable?

## Installation

TODO - write manual and composer install instructions

## Tag documentation

Complete list of tags:

[kb:title](#kbtitle)
[kb:field](#kbfield)
[kb:email](#kbemail)
[kb:tel](#kbtel)
[kb:date](#kbdate)
[kb:js](#kbjs)
[kb:css](#kbcss)
[kb:excerpt](#kbexcerpt)
[kb:prev-title](#kbprev-title--kbnext-title)
[kb:next-title](#kbprev-title--kbnext-title)
[kb:prev](#kbprev--kbnext)
[kb:next](#kbprev--kbnext)
[kb:permalink](#kbpermalink)
[kb:link](#kblink)
[kb:blocks](#kbblocks)
[kb:a](#kba)
[kb:img](#kbimg--kbimage)
[kb:video](#kbvideo)
[kb:vimeo](#kbvimeo)
[kb:youtube](#kbyoutube)
[kb:qr](#kbqr)
[kb:gist](#kbgist)
[kb:svg](#kbsvg)
[kb:section](#kbsection)
[kb:breadcrumb](#kbbreadcrumb)
[kb:tags](#kbtags)
[kb:if](#kbif)
[kb:if-field](#kbif-field)
[kb:foreach](#kbforeach)
[kb:structure](#kbstructure)
[kb:php](#kbphp)
[kb:snippet](#kbsnippet)
[kb:pages](#kbpages)

Working examples of every tag can be found in `site/templates/sandbox.kb.php`.

### kb:title

Outputs the title of the current page, or of another page passed via the `url` attribute.

```html
<kb:title />
```

### kb:field

Outputs the value of a field on the current page. Renders KirbyText by default; pass `kt="false"` to output the raw field value.

```html
<kb:field name="text" />
```

### kb:email

Outputs a `mailto:` link. The address can be a literal string or read from a page field via `field`.

```html
<kb:email address="email@hashandsalt.com" text="Email me" class="email-link" />
<kb:email field="postauthor" text="Email the author" class="email-link" />
```

### kb:tel

Outputs a `tel:` link. The number can be a literal string or read from a page field via `field`.

```html
<kb:tel number="+1234567890" text="Call me" class="tel-link" />
<kb:tel field="telnumber" text="Call me" class="tel-link" />
```

### kb:date

Outputs the current date, or the date stored in a page field via `field`, formatted with `format` (defaults to the plugin's configured date format). When used as a pair, wraps the result in a `<time>` element together with the tag's content as a label.

```html
<kb:date field="postdate" format="d M, Y">Published on</kb:date>
<kb:date format="Y" />
```

### kb:js

Includes one or more JavaScript files via the `files` attribute (comma separated). Bare filenames are resolved against the plugin's configured JS path; use `@auto` to include Kirby's auto-detected template script.

```html
<kb:js files="prism.js, lightbox.js, index.js, @auto" />
```

### kb:css

Includes one or more CSS files via the `files` attribute (comma separated), resolved the same way as `kb:js`.

```html
<kb:css files="prism.css, lightbox.css, index.css, @auto" />
```

### kb:excerpt

Outputs an excerpt of a page field, optionally limited to a number of characters via `chars`.

```html
<kb:excerpt field="text" />
<kb:excerpt field="text" chars="80" />
```

### kb:prev-title / kb:next-title

Outputs the title of the previous/next listed sibling page.

```html
<kb:prev-title />
<kb:next-title />
```

### kb:prev / kb:next

Outputs a link to the previous/next listed sibling page. Used self-closing it links to the sibling's title; used as a pair, its content becomes the link label.

```html
<kb:prev />
<kb:next />

<kb:prev>Previous post - <kb:prev-title /></kb:prev>
<kb:next>Next post - <kb:next-title /></kb:next>
```

### kb:permalink

Outputs a link using a page's UUID-based permalink. Defaults to the current page, or another page passed via `page`.

```html
<kb:permalink page="photography" />
<kb:permalink page="notes" />
```

### kb:link

Outputs a `<link>` tag in the document `<head>`, e.g. for favicons. All attributes besides `href` are passed straight through.

```html
<kb:link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
```

### kb:blocks

Renders a blocks field (via `field`) or a blocks object/field passed via `src`.

```html
<kb:blocks field="blockexample" />
```

### kb:a

Outputs a link. The target can be another page (`url` pointing to a page/URL/expression), and falls back to the current page. The tag's content becomes the link label.

```html
<kb:a url="{{ $page->url() }}">{{ $page->title() }}</kb:a>
<kb:a url="$page->url()">{{ $page->title() }}</kb:a>
```

### kb:img / kb:image

Outputs a responsive `<img>` (or `<picture>`) tag for a file. `src` can be a filename on the current page, an asset path, or a file/expression. Supports resizing/cropping via `width`, `height` and `mode` (`resize` or `crop`), plus `format`, `quality`, `ratio`, `object-fit` and `alt` (falls back to the file's own `alt` field).

```html
<!-- from a file name -->
<kb:image src="team.jpg" mode="crop" quality="80" format="webp" object-fit="cover" ratio="16/9" width="1440" height="450" class="team-image" />

<!-- via cover() set in model -->
<kb:if condition="$cover = $page->cover()">
  <kb:image src="$cover" mode="crop" quality="80" format="webp" object-fit="contain" ratio="16/9" width="1440" height="450" class="team-image" alt="$cover->alt()->esc()" />
</kb:if>
```

### kb:video

Outputs an HTML5 `<video>` tag. `src`/`url` can be a filename on the current page or a URL; `poster` accepts a filename or URL for the poster image.

```html
<kb:video src="forest.mp4" poster="forest.jpg" controls="true" width="640" height="360" />
```

### kb:vimeo

Outputs a responsive Vimeo embed. Any attribute matching a valid Vimeo player parameter is passed through to the embed URL.

```html
<kb:vimeo url="https://vimeo.com/253905163" width="640" height="360" />
```

### kb:youtube

Outputs a responsive YouTube embed. Any attribute matching a valid YouTube player parameter is passed through to the embed URL.

```html
<kb:youtube url="https://www.youtube.com/watch?v=UKMK31-jjhw" width="640" height="360" />
```

### kb:qr

Outputs a QR code image for the given `data` (a URL, string, or page/file object).

```html
<kb:qr data="https://www.getkirby.com" />
```

### kb:gist

Embeds a GitHub Gist via `url`, optionally scoped to a single `file` in the gist.

```html
<kb:gist url="https://gist.github.com/lukaskleinschmidt/cf97ebff8901053df2b085db6d28c7e2" file="blueprint.yaml" />
```

### kb:svg

Inlines the contents of an SVG file. `src` can be a filename on the current page or a root-relative asset path.

```html
<kb:svg src="kirby.svg" />
```

### kb:section

Outputs the title of the current page's parent section, or the site title for top-level pages.

```html
<kb:section />
```

### kb:breadcrumb

Outputs a breadcrumb navigation (`<nav><ol>...</ol></nav>`) from the site down to the current page.

```html
<kb:breadcrumb />
```

### kb:tags

Splits a tags field (via `field`, defaults to `tags`) and outputs each tag as a link filtering the parent page by that tag. Customise the markup with `wraptag`, `breaktag`, `breakclass` and `wrapclass`/`class`.

```html
<kb:tags field="sometags" class="tag-list" wraptag="ul" breaktag="li" breakclass="tag" />
```

### kb:if

Conditionally renders its content based on a `condition` (or `test`) expression. Supports an `<kb:else />` branch. Assignments like `$cover = $page->cover()` store the result for reuse inside the block.

```html
<kb:if condition="$page->hasFiles()">
  <p>The page has files.</p>
<kb:else/>
  <p>The page has no files.</p>
</kb:if>
```

### kb:if-field

Renders its content only if the named field on the current page is not empty.

```html
<kb:if-field name="sometags">
  <p>The page has a tags field named sometags and it's not empty:</p>
  <kb:tags field="sometags" />
</kb:if-field>
```

### kb:foreach

Loops over an iterable expression (`items`/`in`), making each item available under the variable named by `as` (defaults to `item`).

```html
<kb:foreach items="$site->children()->listed()" as="item">
  <p>{{ $item->title()->esc() }}</p>
</kb:foreach>
```

### kb:structure

Loops over a structure field, making each entry available under the variable named by `as` (defaults to `item`). `field` can be a plain field name on the current page, or an expression targeting a field on another page.

```html
<kb:structure field="social" as="platform">
  <li>
    <kb:a url="{{ $platform->url()->esc() }}" target="_blank" rel="noopener noreferrer">
      {{ $platform->platform()->esc() }}
    </kb:a>
  </li>
</kb:structure>
```

### kb:php

Executes raw PHP code, with the current template/snippet data extracted into scope.

```html
<kb:php>
  echo $page->title()->esc();
</kb:php>
```

### kb:snippet

Includes a snippet by name. Attributes other than `name` are passed to the snippet as variables; when used as a pair, the content becomes the snippet's `default` slot.

```html
<kb:snippet name="header" />
```

### kb:pages

Loops over a page's children (or a `section`, or `"site"`), rendering the tag's content (or a default title link) for each. Supports `mode` (`listed`, `unlisted`, `both`), `sort`, `offset`, `limit`, `as`, and `wraptag`/`breaktag`/`class`/`breakclass` for wrapping markup.

```html
<kb:pages section="photography" wraptag="ul" class="page-list-feature" breakclass="page-list-item" breaktag="li" mode="listed" limit="1" />
<kb:pages section="photography" wraptag="ul" class="page-list" breakclass="page-list-item" breaktag="li" mode="listed" offset="1" />
```








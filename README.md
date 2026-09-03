# TXP template language for Kirby

This plugin adds a small Textpattern-inspired template language to Kirby. A
template is rendered as TXP when a matching `.kb.php` file exists in
`site/templates`; existing PHP templates continue to use Kirby's normal
engine. Snippets work the same way: a snippet is rendered as TXP when a
matching `.kb.php` file exists in `site/snippets`, for example
`site/snippets/footer.kb.php` used via `<kb:snippet name="footer" />`;
existing PHP snippets continue to use Kirby's normal snippet engine. Block
snippets (passing content to a slot) are supported the same way as for plain
PHP snippets; the captured content is available as `{{ $slot }}` inside the
`.kb.php` snippet.

Snippets can also be stored in subfolders. Include the path relative to
`site/snippets` when calling them, for example `<kb:snippet name="blocks/video" />`
resolves `site/snippets/blocks/video.kb.php`.

Example `site/templates/txp-demo.kb.php`:

```html
<h1><kb:title /></h1>
<kb:if-field name="text">
  <p><kb:excerpt field="text" chars="140" /></p>
</kb:if-field>
<kb:pages section="notes" limit="5">
  <article><a href="<kb:permlink />"><kb:title /></a></article>
</kb:pages>
```

The `pages` tag accepts a `mode` attribute to control which children are
listed: `listed` (default), `unlisted`, or `both`, for example
`<kb:pages section="notes" mode="unlisted">`. Use `sort="asc"` or
`sort="desc"` to sort the results alphanumerically by title. Use `class` and
`breakclass` to add CSS classes to the wrapping element and each break
element, for example `<kb:pages class="notes-list" breakclass="notes-item">`.
Use `offset` to skip a number of items from the start of the results, for
example `<kb:pages offset="3">` omits the first 3 pages.

Supported tags are `title`, `field`, `link`, `img`, `if`, `else`, `excerpt`, `tags`, `prev_title`, `next_title`,
`permlink`, `section`, `breadcrumb`, `if-field`,
`snippet`, `pages`, and `date`. `prev_title` and `next_title` use Kirby's
`prevListed()` and `nextListed()` methods and output nothing at the beginning
or end of a listed page collection.
`title` outputs the current page's title by default and accepts an optional
`page` attribute to use another page, for example `<kb:title page="about" />`.
`field` outputs the value of the field named by its `name` attribute. Set
`kt="true"` to convert the value from Kirbytext to HTML; `kt` defaults to
`false`.
`tags` renders the current page's comma-separated `tags` field as a
`<ul class="note-tags">` with links to the parent page filtered by each tag.
Use `field` to select a different tag field, `wrapclass` to change the list
class, and `breakclass` to add a class to each tag list item. The legacy
`class` attribute is also supported as an alias for `wrapclass`. Use
`wraptag` and `breaktag` to change the wrapping and item elements; both
default to `ul` and `li`, respectively. Set either to an empty value to omit
that element.
`email` renders a `mailto:` link via Kirby's `Html::email()`. Use its `field`
attribute to pull the address from a page field, for example
`<kb:email field="email" />`. The `address` attribute accepts either a literal
string, for example `<kb:email address="hello@example.com" text="Say hello" />`,
or a `$`-expression evaluated in the current template context, for example
`<kb:email address="$page->email()" />`.
`tel` renders a `tel:` link via Kirby's `Html::tel()`. Use its `field`
attribute to pull the number from a page field, for example
`<kb:tel field="phone" />`. The `number` attribute accepts either a literal
string, for example `<kb:tel number="+1 234 567 890" text="Call us" />`, or a
`$`-expression evaluated in the current template context, for example
`<kb:tel number="$page->phone()" />`.
`date` renders a `<time>` element for a page field, using its `field`
attribute and an optional `format` attribute passed to the field's
`toDate()` method (defaults to `c`), for example
`<kb:date field="date" format="c">Published on</kb:date>`. Any block content
is used as a label before the field's escaped value; `class` is passed
through to the `<time>` tag. When self-closed, for example
`<kb:date field="date" format="d M Y" />`, it outputs just the formatted date
as a plain string, with no surrounding HTML. When the `field` attribute is
omitted, the current date/time is used instead of a page field.
`js` renders one or more script tags via Kirby's `js()` helper. Its `files`
attribute takes a comma-separated list of file paths, which also supports
`@auto` for Kirby's automatic per-template JS discovery, for example
`<kb:js files="assets/js/index.js, @auto" />`.
`css` renders one or more stylesheet tags via Kirby's `css()` helper. Its
`files` attribute takes a comma-separated list of file paths, which also
supports `@auto` for Kirby's automatic per-template CSS discovery, for example
`<kb:css files="assets/css/index.css, @auto" />`.
Values starting with `$` are evaluated in the current template context, so a
page variable can be used dynamically, for example
`<kb:title page="$project" />`.
`a` outputs an anchor for the current page by default, or for the page named
by its optional `url` attribute. The page title is used as the link text unless
the tag has block content, for example `<kb:a url="$project" />` or
`<kb:a url="$project">View project</kb:a>`.
The `url` attribute also accepts a direct URL, and `target` is passed to the
anchor, for example `<kb:a url="{{ $image->url() }}" target="_blank">`.
`img` and `image` render a Kirby image from their `src` attribute, including
the image's default `alt` text. When both `width` and `height` are set, the
image is resized by default. Set `mode="crop"` to crop it instead; the other
supported mode is `mode="resize"`. The optional `format` and `quality`
attributes are passed to the selected image operation where supported. Image
expressions starting with `$` are evaluated in the current template context,
for example
`<kb:image src="$project->cover()" width="400" height="500" mode="crop" format="webp" />`.
`video` renders a video embed from its required `url` attribute. It also
accepts a `poster` URL or file expression, plus `options` and `attr`
expressions that resolve to arrays passed to Kirby's `video()` helper. For
example, `<kb:video url="$block->url()" poster="$block->poster()->toFile()"
options="$videoOptions" attr="$videoAttributes" />`.
`vimeo` embeds a Vimeo URL and accepts `options` and `attr` expressions that
resolve to arrays passed to Kirby's `vimeo()` helper, for example
`<kb:vimeo url="$block->url()" options="$vimeoOptions" attr="$videoAttributes" />`.
`youtube` embeds a YouTube URL and accepts `options` and `attr` expressions
that resolve to arrays passed to Kirby's `youtube()` helper, for example
`<kb:youtube url="$block->url()" options="$youtubeOptions" attr="$videoAttributes" />`.
`qr` renders a QR code as inline SVG from its required `data` attribute, which
accepts a literal string or a `$`-expression that resolves to a Kirby content
model, for example `<kb:qr data="$page" />`.
`gist` embeds a GitHub Gist from its required `url` attribute, with an optional
`file` attribute to embed one file from a multi-file Gist, for example
`<kb:gist url="https://gist.github.com/user/gist-id" file="example.php" />`.
All tag attributes can also interpolate expressions, for example
`<kb:img src="{{ $image->url() }}" alt="{{ $image->alt()->esc() }}" />`.
`if` renders its content when the `condition` attribute evaluates to true.
Values starting with `$` are evaluated in the current template context; `test`
is accepted as an alias for `condition`. Use `<kb:else />` for the false branch:

```html
<kb:if condition="$project">
  <kb:a url="$project" />
<kb:else />
  No project available.
</kb:if>
```
`excerpt` uses Kirby's field `excerpt()` method and accepts an optional source
field and excerpt options, for example `<kb:excerpt field="text" chars="140" />`.
Conditional tags support a self-closing `else` branch:

```html
<kb:if-field name="excerpt">
  <kb:excerpt />
<kb:else />
  No excerpt available.
</kb:if-field>
```

`breadcrumb` walks the current page's Kirby parents and
uses Kirby's built-in breadcrumb collection.
Use `foreach` to render a block for each item in an array or collection. The
`items` value is evaluated in the current template context and each item is
available through the variable named by `as`:

```html
<kb:foreach items="$gallery" as="image">
  <img src="<kb:php>echo $image->url();</kb:php>" alt="">
</kb:foreach>
```

Use `structure` as a shortcut for iterating a Kirby structure field:

```html
<kb:structure field="social" as="social">
  <a href="<kb:php>echo $social->url()->esc();</kb:php>">
    <kb:php>echo $social->platform()->esc();</kb:php>
  </a>
</kb:structure>
```

Use `snippet` to render a Kirby snippet, for example
`<kb:snippet name="social" />`. Pass template variables as attributes; values
starting with `$` are evaluated in the current template context:
`<kb:snippet name="pagination" pagination="$notes->pagination()" />`.
Use a block snippet to pass content to Kirby's default `$slot`:

```html
<kb:snippet name="wrapper">
  <p>Content rendered inside the wrapper.</p>
</kb:snippet>
```

The `wrapper` snippet can render that content with `<?= $slot ?>`.
Collections are loaded through Kirby's page API (`children()->listed()`); this
plugin does not use SQL or query the database.

`php` executes raw PHP in the current template context. It provides `$page`,
`$site`, and the template data, and must only be used in trusted template files:

Use `{{ expression }}` to evaluate a variable or method expression inside HTML,
for example `style="--w:{{ $image->width() }}px"`. The result is HTML-escaped.

```html
<kb:php>
echo $page->title()->esc();
</kb:php>
```

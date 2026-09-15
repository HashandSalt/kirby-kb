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

kb:title
kb:field
kb:email
kb:tel
kb:date
kb:js
kb:css
kb:excerpt
kb:prev-title
kb:next-title
kb:prev
kb:next
kb:permalink
kb:link
kb:blocks
kb:a
kb:img
kb:video
kb:vimeo
kb:youtube
kb:qr
kb:gist
kb:svg
kb:section
kb:breadcrumb
kb:tags
kb:if
kb:if-field
kb:foreach
kb:structure
kb:php
kb:snippet
kb:pages


```html
<kb:title />
```








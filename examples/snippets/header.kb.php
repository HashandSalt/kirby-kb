<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>{{ $site->title()->esc() }} | {{ $page->title()->esc() }}</title>


  <kb:css files="assets/css/prism.css, assets/css/lightbox.css, assets/css/index.css, @auto" />

  <kb:link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />


</head>

<body>

  <header class="header">

    <kb:a class="logo" url="{{ $site->url() }}">{{ $site->title()->esc() }}</kb:a>


    <nav class="menu">

      <kb:foreach items="$site->children()->listed()" as="item">
        <kb:if condition="$item->isOpen()">
          <kb:a aria-current="page" url="{{ $item->url() }}">{{ $item->title()->esc() }}</kb:a>
          <kb:else />
          <kb:a url="{{ $item->url() }}">{{ $item->title()->esc() }}</kb:a>
        </kb:if>
      </kb:foreach>

      <kb:snippet name="social" />
    </nav>
  </header>

  <main class="main">
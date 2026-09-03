<kb:snippet name="header" />
<kb:snippet name="intro" />
<kb:snippet name="layouts" field="$page->layout()->toLayouts()" />


<aside class="contact">
  <h2 class="h1">Get in contact</h2>
  <div class="grid" style="--gutter: 1.5rem">

    <section class="column text" style="--columns: 4">
      <h3>Address</h3>
      <kb:field name="address" />

    </section>

    <section class="column text" style="--columns: 4">
      <h3>Email</h3>
      <kb:email field="email" />
      <h3>Phone</h3>
      <kb:tel field="phone" />
    </section>

    <section class="column text" style="--columns: 4">

      <h3>On the web</h3>
      <ul class="contact-social">
        <kb:structure field="social" as="platform">
          <li>
            <kb:a url="{{ $platform->url()->esc() }}" target="_blank" rel="noopener noreferrer">
              {{ $platform->platform()->esc() }}
            </kb:a>
          </li>
        </kb:structure>
      </ul>

      
<kb:qr data="https://example.com" />
    </section>
  </div>
</aside>

<kb:snippet name="footer" />
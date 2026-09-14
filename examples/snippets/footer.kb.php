</main>

<footer class="footer">
  <div class="grid">
    <div class="column" style="--columns: 8">
      <h2><kb:a url="https://getkirby.com">Made with Kirby</kb:a></h2>
      <p>
        Kirby: the file-based CMS that adapts to any project, loved by developers and editors alike
      </p>
    </div>
    <div class="column" style="--columns: 2">
      <h2>Pages</h2>

      <kb:pages section="photography" wraptag="ul" class="page-list-feature" breakclass="page-list-item" breaktag="li" mode="listed" limit="1" />
      <kb:pages section="photography" wraptag="ul" class="page-list" breakclass="page-list-item" breaktag="li" mode="listed" offset="1" />

    </div>
    <div class="column" style="--columns: 2">
      <h2>Kirby</h2>
      <ul>
        <li><kb:a url="https://getkirby.com">Website</kb:a></li>
        <li><kb:a url="https://getkirby.com/docs">Docs</kb:a></li>
        <li><kb:a url="https://forum.getkirby.com">Forum</kb:a></li>
        <li><kb:a url="https://chat.getkirby.com">Chat</kb:a></li>
        <li><kb:a url="https://github.com/getkirby">GitHub</kb:a></li>
      </ul>
    </div>
  </div>
</footer>

<kb:js files="prism.js, lightbox.js, index.js, @auto" />

</body>

</html>
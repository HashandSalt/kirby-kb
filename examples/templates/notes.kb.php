<kb:snippet name="header" />

<ul class="grid">
  <kb:foreach items="$notes" as="note">
    <li class="column" style="--columns: 4">
      <kb:snippet name="note" note="$note" page="$note" excerpt="true" />
    </li>
  </kb:foreach>
</ul>

<kb:snippet name="pagination" pagination="$notes->pagination()" />

<kb:snippet name="footer" />
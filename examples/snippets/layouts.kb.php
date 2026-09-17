<kb:foreach items="$field" as="layout">
  <section class="grid margin-xl" id="{{ $layout->id() }}" style="--gutter: 1.5rem">
    <kb:foreach items="$layout->columns()" as="column">
      <div class="column" style="--columns:{{ $column->span() }}">
        <div class="text">
          <kb:blocks src="$column->blocks()" />
        </div>
      </div>
    </kb:foreach>
  </section>
</kb:foreach>
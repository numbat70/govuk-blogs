<?php
function pagination($q = null, $mode = null, $uri = null) {
  global $wp_query;

  $_ = $_SERVER['REQUEST_URI'];

  if ($uri !== null) {
    $_SERVER['REQUEST_URI'] = $uri;
    if (isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
  }

  if ($q === null) {
    $q = $wp_query;
  }

  if ($q->is_singular()) {
    $_SERVER['REQUEST_URI'] = $_;
    return;
  }

  /** Stop execution if there's only 1 page */
  if ($q->max_num_pages <= 1) {
    $_SERVER['REQUEST_URI'] = $_;
    return;
  }

  $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
  $max   = intval($q->max_num_pages);

  /** Add current page to the array */
  if ($paged >= 1) {
    $links[] = $paged;
  }

  /** Add the pages around the current page to the array */
  if ($paged >= 3) {
    $links[] = $paged - 1;
  }

  if (($paged + 2) <= $max) {
    $links[] = $paged + 1;
  }

  // Store $wp_query (ugly hack for ugly get_previous/next_posts_link() functions
  $old_wq = $wp_query;
  $wp_query = $q;

  // previous page
  if (get_previous_posts_link()) {

    $uri = previous_posts(false);
    if ($mode) {
      $uri = add_query_arg(['mode' => $mode], $uri);
    }
    printf('<div class="previous">
    <a href="%s" rel="previous">
      Previous <span class="hidden-mobile">page</span>
    </a>
  </div>', $uri);
  }

  // next page
  if (get_next_posts_link()) {

    $uri = next_posts($q->max_num_pages, false);
    if ($mode) {
      $uri = add_query_arg(['mode' => $mode], $uri);
    }
    printf('<div class="next">
    <a href="%s" rel="next">
      Next <span class="hidden-mobile">page</span>
    </a>
  </div>', $uri);

  }

  // Restore $wp_query
  $wp_query = $old_wq;

  echo '<div class="pagination"><ul class="list-inline">' . "\n";

  /** Link to first page, plus ellipses if necessary */
  if (!in_array(1, $links)) {
    $class = 1 == $paged ? ' class="active"' : '';

    $uri = get_pagenum_link(1);
    if ($mode) {
      $uri = add_query_arg(['mode' => $mode], $uri);
    }
    printf('<li%s><a href="%s"><span class="visuallyhidden">Page </span>%s</a></li>' . "\n", $class, esc_url($uri), '1');

    if (!in_array(2, $links)) {
      echo '<li>…</li>';
    }
  }

  /** Link to current page, plus 2 pages in either direction if necessary */
  sort($links);
  foreach ((array) $links as $link) {
    $class = $paged == $link ? ' class="active"' : '';
    $uri = get_pagenum_link($link);
    if ($mode) {
      $uri = add_query_arg(['mode' => $mode], $uri);
    }
    printf('<li%s><a href="%s"><span class="visuallyhidden">Page </span>%s</a></li>' . "\n", $class, esc_url($uri), $link);
  }

  /** Link to last page, plus ellipses if necessary */
  if (!in_array($max, $links)) {
    if (!in_array($max - 1, $links)) {
      echo '<li>…</li>' . "\n";
    }

    $class = $paged == $max ? ' class="active"' : '';
    $uri = get_pagenum_link($max);
    if ($mode) {
      $uri = add_query_arg(['mode' => $mode], $uri);
    }
    printf('<li%s><a href="%s"><span class="visuallyhidden">Page </span>%s</a></li>' . "\n", $class, esc_url($uri), $max);
  }

  echo '</ul></div>' . "\n";

  $_SERVER['REQUEST_URI'] = $_;
}

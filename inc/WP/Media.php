<?php
namespace Vincit\WP\Media;

function svg($path, $classes = "") {
  $data = false;
  $wrap = function ($x) use ($classes) {
    return "<div class='inline-svg $classes'>$x</div>";
  };

  if (file_exists($path)) {
    $data = $wrap(file_get_contents($path));
  } else {
    $data = $wrap(file_get_contents(get_template_directory() . "/dist/img/svg/$path"));
  }

  return $data;
}

/**
 * Returns an image element.
 * Usage: <?=\Vincit\WP\Media\image($image, 'your-size')?>
 *
 * @param mixed $image
 * @param string $size
 * @param boolean $responsive
 */
function image($image, $size = 'medium', $responsive = true) {
  $data = get_image_data($image, $size);

  if (!$data) {
    return false;
  }

  // If the title contains the filename, don't use a title.
  $has_title = strpos($data['src'], $data['title']) > -1 ? false : true;
  $class = $responsive ? 'vincit-image vincit-image--responsive' : 'vincit-image';

  return  \Vincit\WP\tag([
    "<img src='$data[src]'",
    $responsive ? "srcset='$data[srcset]'" : "",
    $has_title ? "title='$data[title]'" : "",
    "class='$class'",
    "alt='$data[alt]'>"
  ]);
}

/**
 * Returns an image element with captions.
 *
 * @param mixed $image
 * @param string $size
 * @param boolean $responsive
 */
function captioned_image($image, $size, $responsive = true) {
  $image = image($image, $size, $responsive);

  if (!$image) {
    return false;
  }

  $caption = get_image_data($image, $size)['caption'];

  return \Vincit\WP\tag([
    "<figure class='vincit-captioned'>",
      $image,
      "<figcaption class='vincit-captioned__caption'>",
        $caption,
      "</figcaption>",
    "</figure>"
  ]);
}

/**
 * Return all relevant data for an image as an array.
 *
 * @param mixed $image
 * @param string $size
 */
function get_image_data($image, $size = 'medium') {
  if (is_array($image)) {
    $id = $image['ID'];
  } else if (is_numeric($image)) {
    $id = absint($image);
  } else {
    throw new \Exception('$image must be an array or id');
    return false;
  }

  // Cache the call so we won't have to fetch the data again and again...

  $key = "vincit_gid_$id";
  $is_dev = defined("WP_DEBUG") ? WP_DEBUG : false;
  if ($is_dev) {
    $transient = get_transient($key);
  } else {
    $transient = false;
  }

  if ($transient) {
    return $transient;
  } else {
    $attachment = get_post($id);
    $data = [
      'src' => wp_get_attachment_image_url($id, $size),
      'srcset' => wp_get_attachment_image_srcset($id, $size),
      'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
      'caption' => $attachment->post_excerpt,
      'description' => $attachment->post_content,
      'title' => $attachment->post_title
    ];

    set_transient(
      $key,
      $data,
      apply_filters(
        'vincit_get_image_data_transient',
        MINUTE_IN_SECONDS
      )
    );

    return $data;
  }
}
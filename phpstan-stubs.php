<?php

declare(strict_types=1);

// cspell:ignore tgmpa

/**
 * Declarations of the functions that other plugins and the theme provide at runtime, so
 * PHPStan can resolve the calls. Scanned by PHPStan only; never loaded by WordPress.
 */

/**
 * Advanced Custom Fields: reads a field value.
 *
 * @param  string  $selector  The field name or key.
 * @param  mixed  $post_id  The post, term or option the value belongs to.
 * @param  bool  $format_value  Whether to apply formatting logic.
 */
function get_field(string $selector, mixed $post_id = false, bool $format_value = true): mixed {}

/**
 * TGM Plugin Activation: registers the plugins the plugin requires.
 *
 * @param  array<int|string, mixed>  $plugins  The plugins to register.
 * @param  array<string, mixed>  $config  The library configuration.
 */
function tgmpa(array $plugins, array $config = []): void {}

/**
 * Better Image Sizes: returns a resized attachment image.
 *
 * @param  mixed  $attachment_id  The attachment.
 * @param  mixed  $size  The width and height.
 */
function bis_get_attachment_image_src(mixed $attachment_id, mixed $size): mixed {}

=== Collage Gallery ===
Contributors: alekseysolo
Tags: collage, gallery, attachments, images, photo, media, photoswipe
Requires at least: 3.2
Tested up to: 4.1
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create responsive collage gallery from images attached to the post.

== Description ==

Plugin automatically create responsive collage gallery (like Google, Flickr, VK.com...) from images attached to the post with lightbox.

**Features**

* Create **collage gallery** with **caption** from images, attached to the post automatically or manual with shotcode `[collage_gallery]` with parameters `[collage_gallery photo = "1,3,4-12"]`, where `1,3,4-12` - number of attached images: first, third and from 4-th till 12-th.
* **!!!** Open lightbox (`PhotoSwipe`: touch, mobile, responsive), when clicked on image in collage. 
* Select the pages types on which will be added collage: `is_tax()`, `is_single()`, `is_front_page()`.
* Create collage only if post has `post_meta` with given name.
* Some collage gallery settings: caption, collage row height, last images...

**Links**

[Plugin site](http://ukraya.ru/collage-gallery/ "Collage Gallery official site") | [Plugin Support](http://ukraya.ru/collage-gallery/support "Collage Gallery Plugin Support") | [Author site](http://ukraya.ru "Plugin Author Site")

**Thanks**

* Collage based on jQuery plugin "Justified Gallery" by `miromannino` [github](https://github.com/miromannino/Justified-Gallery "Justified Gallery by miromannino").
* [PhotoSwipe](http://photoswipe.com/ "PhotoSwipe official site") - Responsive JavaScript image Gallery by Dmitry Semenov. 

== Installation ==

Upload the Collage Gallery plugin to your blog and Activate it.

== Screenshots ==

1. Collage in post.
2. Collage in post with another value of parameter collage_row_height.
3. Collage Gallery Settings page.

== Changelog ==

= 0.2 / 2015-03-28 =
* Added PhotoSwipe - touch, mobile, responsive lightbox.

= 0.1 / 2015-03-26 =
* First stable release.
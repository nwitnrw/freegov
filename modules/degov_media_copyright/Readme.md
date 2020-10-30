#Computed copyright and caption 

`field_media_copyright` provides a common template for all media items with field_copyright. 

This field can is also available on some paragraph types containing media. If they have the fields 

* `field_media_reference_media`
* `field_slide_media`

If the `field_copyright` is not available in media entity (referenced or direct) the field will not be available.

### Template 

Default template `degov-media-copyright.html.twig`.

```html
<span class="media-copyright media-copyright--image">CAPTION&nbsp;/ <span class="media-copyright--copyright-label">©</span>COPYRIGHT</span>
```

* COPYRIGHT: Media Item copyright
* CAPTION: Media Item caption or caption provided by `paragraph->field_caption_override`

### Paragraphs

`{{ field_media_copyright }}` is available in paragraph and media templates. 

### Subtheme styling broken?

In previous releases have been inconsistencies in stylings. If fields have already been styled, such as 

* `.audio__copyright`
* `.video__info__wrap ... .video__copyright`
* `.image__caption ... .image__copyright`

you should replace all of them and style the following selectors:

* `.media-copyright` --> global style for all media copyrights
* `.media-copyright--TYPE` (TYPE = Media bundle. E.g `.media-copyright--image`)


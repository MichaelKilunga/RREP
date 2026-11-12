---
paths:
  - app/Http/Controllers/SettingController.php
---

# Http Controllers

## Website CMS images: upload slots go through storeLandingImage()
Each CMS image has a `<key>_file` upload plus a `<key>` URL box that always posts the current image. updateLandingPage() stores uploads via storeLandingImage() (validated with upload_limit_kb + imageMessages, refused files named in the error flash while the rest still saves) and then excludes uploaded slots from the generic text loop, which reads `$request->input()` only. Add new image slots to `$imageSlots`; never let the text loop write a slot that just got an upload (that bug left hero slides 2/3 unreplaceable).

# Ping

A toy PNG encoder/decoder written in PHP. This is not worth using for anything except curiosity.
PHP is not well suited to handling binary data, and memory usage soon becomes an issue for anything but the
smallest of images.

# Known issues

- Not all PNG formats are supported by the encoder.
- There's an issue with somewhere with some 8 and 16 bits per channel images.
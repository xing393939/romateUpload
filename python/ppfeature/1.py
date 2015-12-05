import hashlib
import os
#path = "C:\\Users\\Public\\Videos\\Sample Videos\\1.wmv"
path = "C:\\1.html"

h = hashlib.sha1()
size = os.path.getsize(path)
print size
with open(path, 'rb') as stream:
    if size < 0xFFFF:
        h.update(stream.read())
    else:
        h.update(stream.read(0x3000))
        stream.seek(size/5)
        h.update(stream.read(0x3000))
        stream.seek(2*size/5)
        h.update(stream.read(0x3000))
        stream.seek(3*size/5)
        h.update(stream.read(0x3000))
        stream.seek(size-0x3000)
        h.update(stream.read(0x3000))
print h.hexdigest()


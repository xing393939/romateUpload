import hashlib
import os
#path = "C:\\Users\\Public\\Videos\\Sample Videos\\1.wmv"
path = "C:\\1.html"

h = hashlib.sha1()
size = os.path.getsize(path)
with open(path, 'rb') as stream:
    if size < 0xFFFF:
        h.update(stream.read())
    else:
        h.update(stream.read(0x3000))
        print 0, h.hexdigest()

        stream.seek(size/5)
        h.update(stream.read(0x3000))
        print 1*size/5, h.hexdigest()

        stream.seek(2*size/5)
        h.update(stream.read(0x3000))
        print 2*size/5, h.hexdigest()

        stream.seek(3*size/5)
        h.update(stream.read(0x3000))
        print 3*size/5, h.hexdigest()

        stream.seek(size-0x3000)
        h.update(stream.read(0x3000))
print size-0x3000, h.hexdigest()


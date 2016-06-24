import hashlib
import os
mypath = 'C:\\Esko\\bg_data_system_v010\\1.mp4'

def ppfeature(path):
	h = hashlib.sha1()
	size = os.path.getsize(path)
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
	return h.hexdigest()

def md5(path, block_size=2**20):
    md5 = hashlib.md5()
    with open(path, 'rb') as stream:
        data = stream.read(block_size)
        while data:
            md5.update(data)
            data = stream.read(block_size)
    return md5.hexdigest()

def feature_xunlei_gcid(path):
    h = hashlib.sha1()
    size = os.path.getsize(path)
    psize = 0x40000
    while size / psize > 0x200:
        psize = psize << 1
    with open(path, 'rb') as stream:
        data = stream.read(psize)
        while data:
            h.update(hashlib.sha1(data).digest())
            data = stream.read(psize)
    return h.hexdigest().upper()

def feature_xunlei_cid(path):
	h = hashlib.sha1()
	size = os.path.getsize(path)
	with open(path, 'rb') as stream:
		if size < 0xF000:
			h.update(stream.read())
		else:
			h.update(stream.read(0x5000))
			stream.seek(size/3)
			h.update(stream.read(0x5000))
			stream.seek(size-0x5000)
			h.update(stream.read(0x5000))
	return h.hexdigest()

if __name__ == '__main__':

	print feature_xunlei_cid(mypath)
	print feature_xunlei_gcid(mypath)
	print md5(mypath)
	print ppfeature(mypath)
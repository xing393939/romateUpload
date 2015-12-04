import hashlib
import os

def gcid_hash_file(path):
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

if __name__ == '__main__':
	import sys
	sys.argv
	if len(sys.argv) >= 2:
		args = sys.argv[1:2]
		sys.stdout.write(gcid_hash_file(args[0]))
	else:
		print "please input the filePath"
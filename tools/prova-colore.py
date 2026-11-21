"""
Prepara "Prova il colore" partendo da una foto di mano con smalto in tinta unita.

Come funziona: nella foto ogni pixel dell'unghia è  colore dello smalto × luce  + riflesso bianco.
Lo script separa questi pezzi; il sito poi rifà il calcolo con il colore scelto (da-caricare/assets/js/site.js),
così ombre, curvatura, riflessi e glitter restano quelli veri della foto.

Crea nella cartella di uscita:
  mano.webp   la foto
  mappa.png   R = luce (0..2), G = riflesso, B = luce sfumata (per l'opaco), A = quanto il pixel è unghia
  forma.png   solo la forma delle unghie
  prova.json  il colore originale dello smalto (serve per toglierlo e mettere quello nuovo)

Uso:
  python3 tools/prova-colore.py FOTO CARTELLA_USCITA [tinta_min tinta_max]

La tinta (0-360) è quella dello smalto nella foto: per il verde salvia 120-210.
Foto ideale: mano ferma, luce naturale, smalto colorato e uniforme, sfondo di un'altra tinta, nitida e non compressa.
"""
import sys
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

foto, uscita = sys.argv[1], sys.argv[2]
hmin, hmax = (float(sys.argv[3]), float(sys.argv[4])) if len(sys.argv) > 4 else (120.0, 210.0)

im = Image.open(foto).convert('RGB')
W, H = im.size
a = np.asarray(im).astype(np.float64) / 255
lin = np.where(a <= 0.04045, a / 12.92, ((a + 0.055) / 1.055) ** 2.4)
Y = lin @ [0.2126, 0.7152, 0.0722]
r, g, b = a[..., 0], a[..., 1], a[..., 2]
mx, mn = a.max(-1), a.min(-1)
d = mx - mn + 1e-6
h = np.where(mx == r, ((g - b) / d) % 6, np.where(mx == g, (b - r) / d + 2, (r - g) / d + 4)) * 60
s = d / (mx + 1e-6)


def img(m):
    return Image.fromarray((np.clip(m, 0, 1) * 255).astype(np.uint8))


def arr(i):
    return np.asarray(i).astype(np.float64) / 255


def box(x, rad):
    """media su un quadrato (2r+1)x(2r+1), veloce con le somme cumulative"""
    p = np.pad(x, ((rad + 1, rad), (rad + 1, rad)), mode='edge')
    c = p.cumsum(0).cumsum(1)
    k = 2 * rad + 1
    tot = c[k:, k:] - c[:-k, k:] - c[k:, :-k] + c[:-k, :-k]
    return tot / (k * k)


def guidato(guida, p, rad, eps):
    """filtro guidato (He et al.): ammorbidisce p seguendo i bordi della guida"""
    mI, mp = box(guida, rad), box(p, rad)
    var = box(guida * guida, rad) - mI * mI
    cov = box(guida * p, rad) - mI * mp
    A = cov / (var + eps)
    B = mp - A * mI
    return box(A, rad) * guida + box(B, rad)


def morfo(x, filtro, n):
    return arr(img(x).filter(filtro(n)))


# 1. forma grezza: la tinta dello smalto, fatta crescere sui pixel vicini della stessa tinta, senza la pelle
core = (h > hmin) & (h < hmax) & (s > 0.12) & (mx > 0.30)
cresce = morfo(morfo(core.astype(float), ImageFilter.MinFilter, 3), ImageFilter.MaxFilter, 3) > 0.5
simile = (h > hmin - 30) & (h < hmax + 30) & (s > 0.04) & (mx > 0.10)
pelle = ((h < 55) | (h > 320)) & (s > 0.22) & (mx > 0.30)
for _ in range(10):
    vicino = morfo(cresce.astype(float), ImageFilter.MaxFilter, 3) > 0.5
    cresce = cresce | (vicino & simile & ~pelle)
# riempi i buchi (riflessi, glitter): ciò che lo sfondo non raggiunge è unghia
f = img(morfo(morfo(cresce.astype(float), ImageFilter.MaxFilter, 3), ImageFilter.MinFilter, 3)).convert('L')
for x, y in [(0, 0), (W - 1, 0), (0, H - 1), (W - 1, H - 1)]:
    if f.getpixel((x, y)) == 0:
        ImageDraw.floodfill(f, (x, y), 128)
grezza = (np.asarray(f) != 128).astype(float)
grezza = morfo(morfo(grezza, ImageFilter.MinFilter, 7), ImageFilter.MaxFilter, 7)  # via i puntini isolati

# 1b. includi il bordino scuro dello smalto: pixel vicini ancora più freddi (verdi/azzurri) della pelle
rosso_verde = a[..., 0] - (a[..., 1] + a[..., 2]) / 2   # pelle > 0, smalto salvia < 0
for _ in range(3):
    vicino = morfo(grezza, ImageFilter.MaxFilter, 3) > 0.5
    grezza = np.maximum(grezza, (vicino & (rosso_verde < 0.035) & ~pelle).astype(float))

# 2. bordo preciso: il filtro guidato aggancia la forma ai bordi veri (luminosità a piena risoluzione)
sicuro_dentro = morfo(grezza, ImageFilter.MinFilter, 11) > 0.5
sicuro_fuori = morfo(grezza, ImageFilter.MaxFilter, 5) < 0.5
guida = np.sqrt(Y)
alfa = guidato(guida, grezza, 5, 2e-3)
alfa = guidato(guida, np.clip(alfa, 0, 1), 2, 1e-3)
alfa = np.clip((alfa - 0.12) / 0.76, 0, 1)
alfa[sicuro_dentro] = 1
alfa[sicuro_fuori] = 0
alfa = arr(img(alfa).filter(ImageFilter.GaussianBlur(0.6)))

# 3. luce e riflessi: la luce sull'unghia cambia piano piano, i riflessi sono picchi chiari sopra di essa
dentro = alfa > 0.9
Ymed = float(np.median(Y[dentro]))
scala = Ymed * 3
Yd = np.where(alfa > 0.05, Y, Ymed) / scala
aperta = morfo(morfo(Yd, ImageFilter.MinFilter, 9), ImageFilter.MaxFilter, 9)  # toglie i picchi chiari
morbida = np.maximum(arr(img(aperta).filter(ImageFilter.GaussianBlur(6))), aperta) * scala
riflesso = np.clip(Y - morbida * 1.08 - 0.03, 0, None)          # solo i picchi chiari veri, non il rumore della foto
riflesso = arr(img(np.sqrt(np.clip(riflesso, 0, 1))).filter(ImageFilter.GaussianBlur(0.7))) ** 2 * alfa
luce = np.clip((Y - riflesso) / Ymed, 0, 2)
luce_morbida = np.clip(morbida / Ymed, 0, 2)

mappa = np.dstack([luce / 2, np.sqrt(np.clip(riflesso, 0, 1)), luce_morbida / 2, alfa])
Image.fromarray((np.clip(mappa, 0, 1) * 255 + 0.5).astype(np.uint8), 'RGBA').save(f'{uscita}/mappa.png', optimize=True)
Image.fromarray((np.dstack([np.ones_like(alfa), alfa]) * 255).astype(np.uint8), 'LA').save(f'{uscita}/forma.png', optimize=True)
im.save(f'{uscita}/mano.webp', quality=88)
# colore originale dello smalto (lineare, a luce 1): il sito lo toglie e mette quello scelto
ok = dentro & (riflesso < 0.005) & (luce > 0.3)
smalto = np.median((lin[ok] - riflesso[ok, None]) / luce[ok, None], axis=0)
import json
with open(f'{uscita}/prova.json', 'w') as fh:
    json.dump({'smalto': [round(float(x), 4) for x in smalto], 'larghezza': W, 'altezza': H}, fh)
print(f'{W}x{H}, unghie {dentro.mean() * 100:.1f}% della foto, luce media {Ymed:.3f}')

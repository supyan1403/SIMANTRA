import os, base64
import numpy as np
from PIL import Image, ImageDraw, ImageFont, ImageFilter

base_dir = os.path.abspath('public/images')

# 1. LOAD AND PROCESS KENDI
src_path = r'C:\Users\MyBook Z Series\.gemini\antigravity-ide\brain\d4ec96b7-31e8-4cc8-babf-710df21d9e06\kendi_hd_dark_bg_1787637567564.jpg'
src_img = Image.open(src_path).convert('RGB')
arr = np.array(src_img, dtype=np.float32)

# Fix glare/speckles on the lower left body
yy, xx = np.ogrid[:arr.shape[0], :arr.shape[1]]
glare_patch = (yy >= 730) & (yy <= 860) & (xx >= 420) & (xx <= 500)
is_glare = glare_patch & (arr[:, :, 0] > 80) & (arr[:, :, 1] > 100)
arr[is_glare, 0] = 12.0
arr[is_glare, 1] = 52.0
arr[is_glare, 2] = 68.0

r, g, b = arr[:, :, 0], arr[:, :, 1], arr[:, :, 2]
max_c = np.maximum(np.maximum(r, g), b)

alpha = np.zeros_like(max_c)
alpha[max_c >= 25] = 255.0
soft = (max_c >= 8) & (max_c < 25)
alpha[soft] = ((max_c[soft] - 8.0) / 17.0) * 255.0

# Ensure full opacity inside the entire kendi body
kendi_core = (yy > 80) & (yy < 890) & (xx > 180) & (xx < 820) & (max_c > 10)
alpha[kendi_core] = 255.0

rgba = np.dstack((r, g, b, alpha)).astype(np.uint8)
kendi_fixed = Image.fromarray(rgba, 'RGBA')

# Crop to content
bbox = kendi_fixed.getbbox()
kendi_cropped = kendi_fixed.crop(bbox)
kw, kh = kendi_cropped.size

scale = min(900/kw, 900/kh)
new_kw, new_kh = int(kw * scale), int(kh * scale)
kendi_resized = kendi_cropped.resize((new_kw, new_kh), Image.Resampling.LANCZOS)

final_emblem = Image.new('RGBA', (1000, 1000), (0, 0, 0, 0))
final_emblem.paste(kendi_resized, ((1000 - new_kw) // 2, (1000 - new_kh) // 2), kendi_resized)

# Save 1 & 2
p1 = os.path.join(base_dir, 'logo_kendi_bg.png')
white_emblem_bg = Image.new('RGB', (1000, 1000), (255, 255, 255))
white_emblem_bg.paste(final_emblem, (0, 0), final_emblem)
white_emblem_bg.save(p1, 'PNG', optimize=True)
print("1. logo_kendi_bg.png")

p2 = os.path.join(base_dir, 'logo_kendi_trans.png')
final_emblem.save(p2, 'PNG', optimize=True)
print("2. logo_kendi_trans.png")

# 2. PREMIUM METALLIC 3D TEXT WITH 3X SUPERSAMPLING
S = 3
font_title = ImageFont.truetype('C:/Windows/Fonts/segoeuib.ttf', 78 * S)
font_sub = ImageFont.truetype('C:/Windows/Fonts/segoeuib.ttf', 24 * S)

def create_metallic_gradient(width, height, colors):
    arr_g = np.zeros((height, width, 4), dtype=np.uint8)
    for y in range(height):
        t = y / max(height - 1, 1)
        if t < 0.18:
            f = t / 0.18
            cr = int(240 * (1 - f) + colors[0][0] * f)
            cg = int(250 * (1 - f) + colors[0][1] * f)
            cb = int(255 * (1 - f) + colors[0][2] * f)
        elif t < 0.45:
            f = (t - 0.18) / 0.27
            cr = int(colors[0][0] * (1 - f) + colors[1][0] * f)
            cg = int(colors[0][1] * (1 - f) + colors[1][1] * f)
            cb = int(colors[0][2] * (1 - f) + colors[1][2] * f)
        elif t < 0.55:
            f = (t - 0.45) / 0.10
            peak = abs(f - 0.5) * 2
            cr = int(colors[1][0] * peak + min(colors[1][0] + 70, 255) * (1 - peak))
            cg = int(colors[1][1] * peak + min(colors[1][1] + 70, 255) * (1 - peak))
            cb = int(colors[1][2] * peak + min(colors[1][2] + 70, 255) * (1 - peak))
        elif t < 0.85:
            f = (t - 0.55) / 0.30
            cr = int(colors[1][0] * (1 - f) + colors[2][0] * f)
            cg = int(colors[1][1] * (1 - f) + colors[2][1] * f)
            cb = int(colors[1][2] * (1 - f) + colors[2][2] * f)
        else:
            f = (t - 0.85) / 0.15
            cr = int(colors[2][0] * (1 - f) + min(colors[2][0] + 30, 255) * f)
            cg = int(colors[2][1] * (1 - f) + min(colors[2][1] + 30, 255) * f)
            cb = int(colors[2][2] * (1 - f) + min(colors[2][2] + 30, 255) * f)
        arr_g[y, :] = [min(cr, 255), min(cg, 255), min(cb, 255), 255]
    return Image.fromarray(arr_g, 'RGBA')

def draw_premium_metallic_text(canvas, text, cx, y, font, grad_colors, depth_color, depth=8):
    d = ImageDraw.Draw(canvas)
    bbox = d.textbbox((0, 0), text, font=font)
    tw = bbox[2] - bbox[0]
    th = bbox[3] - bbox[1]
    x = cx - tw // 2

    # Drop shadow
    shadow = Image.new('RGBA', canvas.size, (0, 0, 0, 0))
    sd = ImageDraw.Draw(shadow)
    sd.text((x + depth + 4, y + depth + 4), text, fill=(0, 0, 0, 75), font=font)
    shadow = shadow.filter(ImageFilter.GaussianBlur(radius=6))
    canvas = Image.alpha_composite(canvas, shadow)
    d = ImageDraw.Draw(canvas)

    # 3D extrusion depth
    for i in range(depth, 0, -1):
        t = i / depth
        rc = int(depth_color[0] * (0.6 + 0.4 * t))
        gc = int(depth_color[1] * (0.6 + 0.4 * t))
        bc = int(depth_color[2] * (0.6 + 0.4 * t))
        d.text((x + i, y + i), text, fill=(rc, gc, bc, 255), font=font)

    # Gradient text face
    mask_img = Image.new('L', canvas.size, 0)
    md = ImageDraw.Draw(mask_img)
    md.text((x, y), text, fill=255, font=font)

    gradient = create_metallic_gradient(canvas.size[0], canvas.size[1], grad_colors)
    grad_text = Image.new('RGBA', canvas.size, (0, 0, 0, 0))
    grad_text.paste(gradient, (0, 0), mask_img)
    canvas = Image.alpha_composite(canvas, grad_text)

    # Top specular shine
    hl = Image.new('RGBA', canvas.size, (0, 0, 0, 0))
    hd = ImageDraw.Draw(hl)
    hd.text((x - 1, y - 1), text, fill=(255, 255, 255, 60), font=font)
    canvas = Image.alpha_composite(canvas, hl)

    return canvas, bbox

def build_full_logo(transparent=False):
    W, H = 1200 * S, 980 * S
    if transparent:
        img = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    else:
        img = Image.new('RGBA', (W, H), (255, 255, 255, 255))

    # Paste emblem
    emb_scaled = final_emblem.resize((680 * S, 680 * S), Image.Resampling.LANCZOS)
    img.paste(emb_scaled, ((W - 680 * S) // 2, 10 * S), emb_scaled)

    # Title "S I M A N T R A" - Premium metallic 3D
    spaced_title = "  ".join(list("SIMANTRA"))
    title_y = 705 * S
    img, bbox_t = draw_premium_metallic_text(
        img, spaced_title, W // 2, title_y, font_title,
        grad_colors=[
            (56, 189, 248),   # Sky blue
            (2, 132, 199),    # Teal blue chrome
            (1, 65, 105),     # Deep navy
        ],
        depth_color=(1, 40, 65),
        depth=8
    )

    # Subtitle: Solid Black
    sub_text = "Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra"
    sub_y = title_y + (bbox_t[3] - bbox_t[1]) + 20 * S
    d = ImageDraw.Draw(img)
    bbox_s = d.textbbox((0, 0), sub_text, font=font_sub)
    sw = bbox_s[2] - bbox_s[0]
    # Subtle soft shadow under subtitle for depth
    d.text(((W - sw) // 2 + 1, sub_y + 1), sub_text, fill=(180, 180, 180, 120), font=font_sub)
    d.text(((W - sw) // 2, sub_y), sub_text, fill=(15, 23, 42, 255), font=font_sub)

    # Crop
    content_bottom = sub_y + (bbox_s[3] - bbox_s[1]) + 40 * S
    img = img.crop((0, 0, W, min(content_bottom, H)))

    # Downscale with LANCZOS to 1X
    fw, fh = img.size[0] // S, img.size[1] // S
    img = img.resize((fw, fh), Image.Resampling.LANCZOS)
    return img

# 3. logo_simantra_text_bg.png
p3 = os.path.join(base_dir, 'logo_simantra_text_bg.png')
build_full_logo(transparent=False).convert('RGB').save(p3, 'PNG', optimize=True)
print("3. logo_simantra_text_bg.png")

# 4. logo_simantra_text_trans.png
p4 = os.path.join(base_dir, 'logo_simantra_text_trans.png')
full_trans = build_full_logo(transparent=True)
full_trans.save(p4, 'PNG', optimize=True)
print("4. logo_simantra_text_trans.png")

# 5. logo_simantra_trans.svg
with open(p2, 'rb') as f:
    b64_notext = base64.b64encode(f.read()).decode('ascii')
svg_w, svg_h = final_emblem.size
p5 = os.path.join(base_dir, 'logo_simantra_trans.svg')
with open(p5, 'w', encoding='utf-8') as f:
    f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
    f.write(f'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 {svg_w} {svg_h}" width="100%" height="100%">\n')
    f.write(f'  <image width="{svg_w}" height="{svg_h}" xlink:href="data:image/png;base64,{b64_notext}"/>\n')
    f.write('</svg>\n')
print("5. logo_simantra_trans.svg")

# 6. logo_simantra_text_trans.svg
with open(p4, 'rb') as f:
    b64_text = base64.b64encode(f.read()).decode('ascii')
ft_w, ft_h = full_trans.size
p6 = os.path.join(base_dir, 'logo_simantra_text_trans.svg')
with open(p6, 'w', encoding='utf-8') as f:
    f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
    f.write(f'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 {ft_w} {ft_h}" width="100%" height="100%">\n')
    f.write(f'  <image width="{ft_w}" height="{ft_h}" xlink:href="data:image/png;base64,{b64_text}"/>\n')
    f.write('</svg>\n')
print("6. logo_simantra_text_trans.svg")

print("\n--- ALL 6 ASSETS 100% PERFECTLY SYNCHRONIZED ---")

#!/usr/bin/env python3
from PIL import Image, ImageDraw, ImageFont
import os

# Output path
OUT = 'sitemap.png'
W, H = 1400, 1000

# Colors
PUBLIC_FILL = '#e0f2fe'
PUBLIC_BORDER = '#0284c7'
PUBLIC_TEXT = '#0c4a6e'
STUDENT_FILL = '#dcfce7'
STUDENT_BORDER = '#16a34a'
STUDENT_TEXT = '#14532d'
MANAGER_FILL = '#fff7ed'
MANAGER_BORDER = '#ea580c'
MANAGER_TEXT = '#7c2d12'
EDGE = '#4b5563'  # dark gray for arrows
MUTED = '#6b7280'

BOX_W, BOX_H = 160, 44
R = 6  # corner radius

pages = {
    'public': [
        ('index.php', 'Landing page'),
        ('login.php', 'Login'),
        ('register.php', 'Register'),
        ('reset_password.php', 'Reset password'),
    ],
    'student': [
        ('events.php', 'View all events'),
        ('profile.php', 'My profile'),
    ],
    'manager': [
        ('dashboard.php', 'Overview (stats, events)'),
        ('events_list.php', 'Manage events'),
        ('add_event.php', 'Add new event'),
        ('edit_event.php', 'Edit event'),
        ('students.php', 'View registered students'),
    ]
}

# Positions
center_x = W // 2
top_y = 80
branch_y = 220
col_x = [center_x - 420, center_x, center_x + 420]

# Helper draw funcs

def load_font(size):
    # Prefer Arial/Helvetica, fallback to DejaVu
    for name in ["Arial.ttf", "Helvetica.ttf", "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"]:
        try:
            return ImageFont.truetype(name, size)
        except Exception:
            continue
    return ImageFont.load_default()

font_main = load_font(13)
font_small = load_font(11)

img = Image.new('RGBA', (W, H), 'white')
d = ImageDraw.Draw(img)

# Draw top node (index)
box_x = center_x - BOX_W // 2
box_y = top_y

# Draw rounded rectangle utility

def round_rect(draw, x1, y1, x2, y2, radius, fill, outline):
    draw.rounded_rectangle([x1, y1, x2, y2], radius=radius, fill=fill, outline=outline, width=2)

# Top box (website name / index.php)
round_rect(d, box_x, box_y, box_x + BOX_W, box_y + BOX_H, R, PUBLIC_FILL, PUBLIC_BORDER)
d.text((box_x + BOX_W/2, box_y + 7), 'index.php', font=font_main, fill=PUBLIC_TEXT, anchor='mm')
d.text((box_x + BOX_W/2, box_y + 27), 'Landing page', font=font_small, fill=MUTED, anchor='mm')

# Draw branches
positions = {'public': [], 'student': [], 'manager': []}
for i, key in enumerate(['public', 'student', 'manager']):
    x = col_x[i]
    start_y = branch_y
    for j, (fn, desc) in enumerate(pages[key]):
        bx = x - BOX_W // 2
        by = start_y + j * 100
        if key == 'public':
            fill, border, textcol = PUBLIC_FILL, PUBLIC_BORDER, PUBLIC_TEXT
        elif key == 'student':
            fill, border, textcol = STUDENT_FILL, STUDENT_BORDER, STUDENT_TEXT
        else:
            fill, border, textcol = MANAGER_FILL, MANAGER_BORDER, MANAGER_TEXT
        round_rect(d, bx, by, bx + BOX_W, by + BOX_H, R, fill, border)
        d.text((bx + 12, by + 8), fn, font=font_main, fill=textcol)
        d.text((bx + 12, by + 26), desc, font=font_small, fill=MUTED)
        positions[key].append((bx + BOX_W//2, by + BOX_H//2))

# Draw arrows helper

def arrow(draw, x1, y1, x2, y2, color=EDGE, w=2):
    # draw line
    draw.line((x1, y1, x2, y2), fill=color, width=int(w))
    # arrowhead
    import math
    angle = math.atan2(y2 - y1, x2 - x1)
    ah = 10  # arrowhead size
    p1 = (x2 - ah * math.cos(angle - math.pi/6), y2 - ah * math.sin(angle - math.pi/6))
    p2 = (x2 - ah * math.cos(angle + math.pi/6), y2 - ah * math.sin(angle + math.pi/6))
    draw.polygon([ (x2,y2), p1, p2 ], fill=color)

# Arrows from top index to each branch's first box
for key, pos in positions.items():
    for p in pos:
        # line from bottom of index box to top of child box
        arrow(d, center_x, box_y + BOX_H, p[0], p[1] - BOX_H//2 + 4)
        break

# Additional navigation arrows per spec
# login -> student or manager dashboards (role redirect)
# locate positions: public login is pages['public'][1]
login_pos = positions['public'][1]
# student events target
student_events_pos = positions['student'][0]
# manager dashboard target
manager_dashboard_pos = positions['manager'][0]
arrow(d, login_pos[0], login_pos[1] + BOX_H//2 - 6, student_events_pos[0], student_events_pos[1] - BOX_H//2 + 6)
arrow(d, login_pos[0], login_pos[1] + BOX_H//2 - 6, manager_dashboard_pos[0], manager_dashboard_pos[1] - BOX_H//2 + 6)

# register -> login
register_pos = positions['public'][2]
arrow(d, register_pos[0], register_pos[1] + BOX_H//2 - 6, login_pos[0], login_pos[1] - BOX_H//2 + 6)

# reset_password -> login (after reset)
reset_pos = positions['public'][3]
arrow(d, reset_pos[0], reset_pos[1] + BOX_H//2 - 6, login_pos[0], login_pos[1] - BOX_H//2 + 6)

# manager flow: dashboard -> events_list, students
dash_pos = manager_dashboard_pos
events_list_pos = positions['manager'][1]
students_pos = positions['manager'][-1]
arrow(d, dash_pos[0], dash_pos[1] + BOX_H//2 - 6, events_list_pos[0], events_list_pos[1] - BOX_H//2 + 6)
arrow(d, dash_pos[0], dash_pos[1] + BOX_H//2 - 6, students_pos[0], students_pos[1] - BOX_H//2 + 6)

# events_list -> add_event, edit_event
add_pos = positions['manager'][2]
edit_pos = positions['manager'][3]
arrow(d, events_list_pos[0], events_list_pos[1] + BOX_H//2 - 6, add_pos[0], add_pos[1] - BOX_H//2 + 6)
arrow(d, events_list_pos[0], events_list_pos[1] + BOX_H//2 - 6, edit_pos[0], edit_pos[1] - BOX_H//2 + 6)

# student: events -> profile
arrow(d, student_events_pos[0], student_events_pos[1] + BOX_H//2 - 6, positions['student'][1][0], positions['student'][1][1] - BOX_H//2 + 6)

# Legend at bottom
lg_x = 80
lg_y = H - 120
box_legend_w = 18
box_legend_h = 12
# public
d.rectangle([lg_x, lg_y, lg_x + box_legend_w, lg_y + box_legend_h], fill=PUBLIC_FILL, outline=PUBLIC_BORDER)
d.text((lg_x + box_legend_w + 8, lg_y - 2), 'blue = public', font=font_small, fill=PUBLIC_TEXT)
# student
lg_x += 180
d.rectangle([lg_x, lg_y, lg_x + box_legend_w, lg_y + box_legend_h], fill=STUDENT_FILL, outline=STUDENT_BORDER)
d.text((lg_x + box_legend_w + 8, lg_y - 2), 'green = student', font=font_small, fill=STUDENT_TEXT)
# manager
lg_x += 180
d.rectangle([lg_x, lg_y, lg_x + box_legend_w, lg_y + box_legend_h], fill=MANAGER_FILL, outline=MANAGER_BORDER)
d.text((lg_x + box_legend_w + 8, lg_y - 2), 'orange = manager', font=font_small, fill=MANAGER_TEXT)

# Save
img.save(OUT, dpi=(150,150))
print('Saved', OUT, 'size', img.size)

# Basic verification printout
all_pages = []
for k,v in pages.items():
    for fn,desc in v:
        all_pages.append(fn)
print('Pages included:', ', '.join(all_pages))

# EOF

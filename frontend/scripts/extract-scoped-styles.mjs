import fs from 'fs'
import path from 'path'

const root = path.resolve('app')
const dirs = ['components', 'pages', 'layouts']
const files = []

for (const d of dirs) {
  const walk = (p) => {
    for (const e of fs.readdirSync(p, { withFileTypes: true })) {
      const f = path.join(p, e.name)
      if (e.isDirectory()) walk(f)
      else if (e.name.endsWith('.vue')) files.push(f)
    }
  }
  walk(path.join(root, d))
}

const scoped = files.filter((f) => /<style scoped>/.test(fs.readFileSync(f, 'utf8')))
const cssByFile = {}
const tailwind = []

for (const f of scoped.sort()) {
  const content = fs.readFileSync(f, 'utf8')
  const m = content.match(/<style scoped>([\s\S]*?)<\/style>/)
  const rel = path.relative(root, f).replace(/\\/g, '/')
  cssByFile[rel] = m ? m[1].trim() : ''

  for (const [, cls] of content.matchAll(/class="([^"]+)"/g)) {
    if (cls.length >= 80) {
      tailwind.push({ file: rel, len: cls.length, sample: cls })
    }
  }
}

const out = { count: scoped.length, cssByFile, tailwind: tailwind.sort((a, b) => b.len - a.len) }
fs.writeFileSync('scripts/scoped-styles-output.json', JSON.stringify(out, null, 2), 'utf8')
console.log('Wrote', scoped.length, 'files; tailwind hits:', out.tailwind.length)

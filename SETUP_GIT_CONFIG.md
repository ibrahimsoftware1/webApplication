# Setting Up Git Config for Each Team Member

## ⚠️ Important: Git Author Information

**GitHub shows commits by the Git author (name + email), NOT by branch name!**

If everyone uses the same Git config, all commits will appear as from the same person, even on different branches.

## ✅ Solution: Set Git Config Per Person

Each person needs to configure their Git identity **before making commits** on their branch.

---

## 🔧 How to Set Your Git Config

### **Option 1: Set for This Repository Only (Recommended)**

Each person should run these commands **on their branch**:

#### **For Ibrahim:**
```bash
git checkout ibrahim
git config user.name "Ibrahim Qader Braim"
git config user.email "ichoman88@gmail.com"
```

#### **For Daban:**
```bash
git checkout daban
git config user.name "Daban [Full Name]"
git config user.email "daban@example.com"  # Use Daban's GitHub email
```

#### **For Rasa:**
```bash
git checkout rasa
git config user.name "Rasa [Full Name]"
git config user.email "rasa@example.com"  # Use Rasa's GitHub email
```

#### **For Abdullah:**
```bash
git checkout abdullah
git config user.name "Abdullah [Full Name]"
git config user.email "abdullah@example.com"  # Use Abdullah's GitHub email
```

#### **For Ahmad:**
```bash
git checkout ahmad
git config user.name "Ahmad [Full Name]"
git config user.email "ahmad@example.com"  # Use Ahmad's GitHub email
```

**Note:** Replace `[Full Name]` and email addresses with actual values!

---

## 📧 Finding Your GitHub Email

Each person should use the email associated with their GitHub account:

1. Go to GitHub.com → Settings → Emails
2. Find your email address (or use the GitHub no-reply email)
3. Use that email in the Git config

**GitHub No-Reply Email Format:**
- `username@users.noreply.github.com`
- Or: `ID+username@users.noreply.github.com`

---

## ✅ Verify Your Config

After setting, verify it worked:

```bash
git config user.name
git config user.email
```

---

## 🔄 If You Already Committed with Wrong Config

If someone already committed with the wrong name/email, you can fix it:

### **For the Last Commit:**
```bash
git commit --amend --author="Your Name <your-email@example.com>"
```

### **For Multiple Commits (Advanced):**
Use `git rebase` or `git filter-branch` (be careful with this!)

---

## 🎯 Best Practice Workflow

1. **Switch to your branch:**
   ```bash
   git checkout ibrahim  # or your branch
   ```

2. **Set your Git config (one time per branch):**
   ```bash
   git config user.name "Your Name"
   git config user.email "your-email@example.com"
   ```

3. **Verify:**
   ```bash
   git config user.name
   git config user.email
   ```

4. **Make your commits:**
   ```bash
   git add [files]
   git commit -m "feat: Your work"
   ```

5. **Push:**
   ```bash
   git push origin [your-branch]
   ```

---

## 📊 How GitHub Shows Commits

- ✅ **Correct:** Each commit shows the author's name and email
- ❌ **Wrong:** All commits show the same person (if using same config)

**Example on GitHub:**
- Commit by Ibrahim: "Ibrahim Qader Braim committed..."
- Commit by Daban: "Daban [Name] committed..."
- etc.

---

## ⚠️ Important Notes

1. **Git config is per-repository** - Setting it in this repo won't affect other repos
2. **Set it once per branch** - It persists for that branch
3. **Email must match GitHub** - For commits to link to GitHub profile
4. **Branch names don't matter** - Only the commit author matters

---

## 🆘 Quick Reference

```bash
# Check current config
git config user.name
git config user.email

# Set config (for this repo only)
git config user.name "Your Name"
git config user.email "your-email@example.com"

# Set global config (affects all repos - not recommended for this)
git config --global user.name "Your Name"
git config --global user.email "your-email@example.com"
```

---

## 🎓 For Your Teacher

If everyone sets their Git config correctly:
- ✅ Each commit will show the correct author
- ✅ GitHub will display different contributors
- ✅ The teacher will see 5 different people contributed
- ✅ Branch names are just organizational - they don't affect authorship

**The teacher will be able to see:**
- Who made each commit (by name/email)
- Which branch each commit is on
- The commit history per person

